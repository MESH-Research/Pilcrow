<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class SubmissionPaginatorTest extends ApiTestCase
{
    use RefreshDatabase;

    private const SUBMISSIONS_QUERY = '
        query (
            $first: Int!,
            $page: Int,
            $publication: [ID!],
            $status: [SubmissionStatus!],
            $orderBy: [QuerySubmissionsOrderByOrderByClause!]
        ) {
            submissions(
                first: $first,
                page: $page,
                publication: $publication,
                status: $status,
                orderBy: $orderBy
            ) {
                paginatorInfo {
                    total
                    currentPage
                }
                statusCounts {
                    status
                    count
                }
                data {
                    id
                    title
                    status
                }
            }
        }
    ';

    /**
     * Helper to create a publication with an editor and multiple submissions
     * at various statuses.
     */
    private function seedPublicationWithSubmissions(): array
    {
        /** @var User $editor */
        $editor = User::factory()->create();
        $this->actingAs($editor);

        $publication = Publication::factory()
            ->hasAttached($editor, [], 'editors')
            ->create(['name' => 'Test Publication']);

        $submitter = User::factory()->create();

        $statuses = [
            ['title' => 'Draft One', 'status' => Submission::DRAFT],
            ['title' => 'Draft Two', 'status' => Submission::DRAFT],
            ['title' => 'Submitted One', 'status' => Submission::INITIALLY_SUBMITTED],
            ['title' => 'Under Review', 'status' => Submission::UNDER_REVIEW],
            ['title' => 'Awaiting Decision', 'status' => Submission::AWAITING_DECISION],
            ['title' => 'Accepted', 'status' => Submission::ACCEPTED_AS_FINAL],
        ];

        $submissions = [];
        foreach ($statuses as $attrs) {
            $submissions[] = Submission::factory()
                ->for($publication)
                ->hasAttached($submitter, [], 'submitters')
                ->create($attrs);
        }

        return compact('editor', 'publication', 'submitter', 'submissions');
    }

    public function test_status_counts_returns_all_statuses_for_publication(): void
    {
        ['publication' => $pub] = $this->seedPublicationWithSubmissions();

        $response = $this->graphQL(self::SUBMISSIONS_QUERY, [
            'first' => 10,
            'publication' => [(string)$pub->id],
        ]);

        $response->assertJsonPath('data.submissions.paginatorInfo.total', 6);

        $counts = collect($response->json('data.submissions.statusCounts'));

        $this->assertEquals(2, $counts->firstWhere('status', 'DRAFT')['count']);
        $this->assertEquals(1, $counts->firstWhere('status', 'INITIALLY_SUBMITTED')['count']);
        $this->assertEquals(1, $counts->firstWhere('status', 'UNDER_REVIEW')['count']);
        $this->assertEquals(1, $counts->firstWhere('status', 'AWAITING_DECISION')['count']);
        $this->assertEquals(1, $counts->firstWhere('status', 'ACCEPTED_AS_FINAL')['count']);
    }

    public function test_status_counts_are_not_affected_by_status_filter(): void
    {
        ['publication' => $pub] = $this->seedPublicationWithSubmissions();

        // Filter table data to only DRAFT submissions
        $response = $this->graphQL(self::SUBMISSIONS_QUERY, [
            'first' => 10,
            'publication' => [(string)$pub->id],
            'status' => ['DRAFT'],
        ]);

        // The paginated data should only contain drafts
        $response->assertJsonPath('data.submissions.paginatorInfo.total', 2);
        $data = $response->json('data.submissions.data');
        $this->assertCount(2, $data);
        foreach ($data as $row) {
            $this->assertEquals('DRAFT', $row['status']);
        }

        // But statusCounts should still reflect ALL statuses for the publication
        $counts = collect($response->json('data.submissions.statusCounts'));
        $this->assertEquals(2, $counts->firstWhere('status', 'DRAFT')['count']);
        $this->assertEquals(1, $counts->firstWhere('status', 'INITIALLY_SUBMITTED')['count']);
        $this->assertEquals(1, $counts->firstWhere('status', 'UNDER_REVIEW')['count']);
        $this->assertEquals(1, $counts->firstWhere('status', 'AWAITING_DECISION')['count']);
        $this->assertEquals(1, $counts->firstWhere('status', 'ACCEPTED_AS_FINAL')['count']);
    }

    public function test_status_counts_are_scoped_to_publication_filter(): void
    {
        ['editor' => $editor, 'publication' => $pub1] = $this->seedPublicationWithSubmissions();

        // Create a second publication with different submissions
        $pub2 = Publication::factory()
            ->hasAttached($editor, [], 'editors')
            ->create(['name' => 'Other Publication']);

        $submitter = User::factory()->create();
        Submission::factory()
            ->for($pub2)
            ->hasAttached($submitter, [], 'submitters')
            ->create(['title' => 'Other Rejected', 'status' => Submission::REJECTED]);

        Submission::factory()
            ->for($pub2)
            ->hasAttached($submitter, [], 'submitters')
            ->create(['title' => 'Other Rejected 2', 'status' => Submission::REJECTED]);

        // Query only pub1 — statusCounts should not include pub2's submissions
        $response = $this->graphQL(self::SUBMISSIONS_QUERY, [
            'first' => 10,
            'publication' => [(string)$pub1->id],
        ]);

        $counts = collect($response->json('data.submissions.statusCounts'));
        $this->assertNull($counts->firstWhere('status', 'REJECTED'));
        $this->assertEquals(6, $response->json('data.submissions.paginatorInfo.total'));

        // Query only pub2
        $response2 = $this->graphQL(self::SUBMISSIONS_QUERY, [
            'first' => 10,
            'publication' => [(string)$pub2->id],
        ]);

        $counts2 = collect($response2->json('data.submissions.statusCounts'));
        $this->assertEquals(2, $counts2->firstWhere('status', 'REJECTED')['count']);
        $this->assertNull($counts2->firstWhere('status', 'DRAFT'));
    }

    public function test_status_counts_with_no_publication_filter(): void
    {
        $this->seedPublicationWithSubmissions();

        // Query without publication filter — should see all visible submissions
        $response = $this->graphQL(self::SUBMISSIONS_QUERY, [
            'first' => 10,
        ]);

        $counts = collect($response->json('data.submissions.statusCounts'));
        $totalFromCounts = $counts->sum('count');

        $this->assertEquals(
            $response->json('data.submissions.paginatorInfo.total'),
            $totalFromCounts,
            'Sum of statusCounts should equal total submissions'
        );
    }

    public function test_pagination_works_with_status_counts(): void
    {
        ['publication' => $pub] = $this->seedPublicationWithSubmissions();

        // Request page 1 with 2 items per page
        $response = $this->graphQL(self::SUBMISSIONS_QUERY, [
            'first' => 2,
            'page' => 1,
            'publication' => [(string)$pub->id],
        ]);

        $response->assertJsonPath('data.submissions.paginatorInfo.total', 6);
        $response->assertJsonPath('data.submissions.paginatorInfo.currentPage', 1);
        $this->assertCount(2, $response->json('data.submissions.data'));

        // statusCounts should still reflect all 6 submissions
        $counts = collect($response->json('data.submissions.statusCounts'));
        $this->assertEquals(6, $counts->sum('count'));
    }

    public function test_ordering_works(): void
    {
        ['publication' => $pub] = $this->seedPublicationWithSubmissions();

        $response = $this->graphQL(self::SUBMISSIONS_QUERY, [
            'first' => 10,
            'publication' => [(string)$pub->id],
            'orderBy' => [['column' => 'TITLE', 'order' => 'ASC']],
        ]);

        $data = $response->json('data.submissions.data');
        $titles = array_column($data, 'title');

        $sorted = $titles;
        sort($sorted);
        $this->assertEquals($sorted, $titles, 'Results should be sorted by title ascending');
    }

    public function test_visibility_scope_hides_submissions_from_unrelated_users(): void
    {
        // Create a publication and submissions that the acting user has NO role on
        $owner = User::factory()->create();
        $unrelatedPub = Publication::factory()
            ->hasAttached($owner, [], 'editors')
            ->create();

        Submission::factory()
            ->for($unrelatedPub)
            ->hasAttached($owner, [], 'submitters')
            ->create(['status' => Submission::INITIALLY_SUBMITTED]);

        // Act as a different user with no connection to the publication
        $outsider = User::factory()->create();
        $this->actingAs($outsider);

        $response = $this->graphQL(self::SUBMISSIONS_QUERY, [
            'first' => 10,
        ]);

        $response->assertJsonPath('data.submissions.paginatorInfo.total', 0);
        $counts = $response->json('data.submissions.statusCounts');
        $this->assertEmpty($counts);
    }

    public function test_publication_level_status_counts_unaffected_by_query_filters(): void
    {
        ['publication' => $pub] = $this->seedPublicationWithSubmissions();

        $response = $this->graphQL(
            'query ($id: ID!) {
                publication(id: $id) {
                    submission_status_counts {
                        status
                        count
                    }
                }
            }',
            ['id' => (string)$pub->id]
        );

        $counts = collect($response->json('data.publication.submission_status_counts'));
        $this->assertEquals(2, $counts->firstWhere('status', 'DRAFT')['count']);
        $this->assertEquals(1, $counts->firstWhere('status', 'INITIALLY_SUBMITTED')['count']);
        $this->assertEquals(6, $counts->sum('count'));
    }
}
