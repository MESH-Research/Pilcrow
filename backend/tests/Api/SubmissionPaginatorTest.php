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

    public function testStatusCountsReturnsAllStatusesForPublication(): void
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

    public function testStatusCountsAreNotAffectedByStatusFilter(): void
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

    public function testStatusCountsAreScopedToPublicationFilter(): void
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

    public function testStatusCountsWithNoPublicationFilter(): void
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

    public function testPaginationWorksWithStatusCounts(): void
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

    public function testOrderingWorks(): void
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

    public function testVisibilityScopeHidesSubmissionsFromUnrelatedUsers(): void
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

    public function testPublicationLevelStatusCountsUnaffectedByQueryFilters(): void
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
        // Drafts are excluded from publication-level counts.
        $this->assertNull($counts->firstWhere('status', 'DRAFT'));
        $this->assertEquals(1, $counts->firstWhere('status', 'INITIALLY_SUBMITTED')['count']);
        // 6 total minus 2 drafts = 4
        $this->assertEquals(4, $counts->sum('count'));
    }

    private const PUB_SEARCH_QUERY = '
        query ($id: ID!, $search: String) {
            publication(id: $id) {
                submissions(first: 25, search: $search) {
                    paginatorInfo { total }
                    data { title }
                }
            }
        }
    ';

    /**
     * Seed a publication with submissions whose titles, submitters,
     * reviewers, and coordinators are distinctive enough to assert
     * search-prefix targeting.
     *
     * @return array{editor: User, publication: Publication}
     */
    private function seedPublicationForSearch(): array
    {
        /** @var User $editor */
        $editor = User::factory()->create();
        $this->actingAs($editor);

        $publication = Publication::factory()
            ->hasAttached($editor, [], 'editors')
            ->create(['name' => 'Search Pub']);

        $alice = User::factory()->create(['name' => 'Alice Submitter', 'email' => 'alice@example.com']);
        $bob = User::factory()->create(['name' => 'Bob Reviewer', 'email' => 'bob@example.com']);
        $carol = User::factory()->create(['name' => 'Carol Coordinator', 'email' => 'carol@example.com']);
        $stranger = User::factory()->create(['name' => 'Nobody', 'email' => 'nobody@example.com']);

        // Submission 1: the only one whose TITLE contains "Alice"; assigned to stranger only.
        Submission::factory()
            ->for($publication)
            ->hasAttached($stranger, [], 'submitters')
            ->create(['title' => 'A paper about Alice', 'status' => Submission::INITIALLY_SUBMITTED]);

        // Submission 2: submitter is Alice; title doesn't mention her.
        Submission::factory()
            ->for($publication)
            ->hasAttached($alice, [], 'submitters')
            ->create(['title' => 'Quantum Entanglement', 'status' => Submission::INITIALLY_SUBMITTED]);

        // Submission 3: reviewer is Bob; submitter is stranger.
        Submission::factory()
            ->for($publication)
            ->hasAttached($stranger, [], 'submitters')
            ->hasAttached($bob, [], 'reviewers')
            ->create(['title' => 'Photosynthesis', 'status' => Submission::UNDER_REVIEW]);

        // Submission 4: coordinator is Carol; submitter is stranger.
        Submission::factory()
            ->for($publication)
            ->hasAttached($stranger, [], 'submitters')
            ->hasAttached($carol, [], 'reviewCoordinators')
            ->create(['title' => 'Cold Fusion', 'status' => Submission::AWAITING_DECISION]);

        // Submission 5: control — no matching anything.
        Submission::factory()
            ->for($publication)
            ->hasAttached($stranger, [], 'submitters')
            ->create(['title' => 'Unrelated Work', 'status' => Submission::INITIALLY_SUBMITTED]);

        return compact('editor', 'publication');
    }

    public function testSearchTitlePrefixMatchesTitleOnly(): void
    {
        ['publication' => $pub] = $this->seedPublicationForSearch();

        $response = $this->graphQL(self::PUB_SEARCH_QUERY, [
            'id' => (string)$pub->id,
            'search' => 'title:Alice',
        ]);

        $titles = array_column($response->json('data.publication.submissions.data'), 'title');
        $this->assertEquals(['A paper about Alice'], $titles);
    }

    public function testSearchSubmitterPrefixMatchesSubmittersOnly(): void
    {
        ['publication' => $pub] = $this->seedPublicationForSearch();

        $response = $this->graphQL(self::PUB_SEARCH_QUERY, [
            'id' => (string)$pub->id,
            'search' => 'submitter:alice',
        ]);

        $titles = array_column($response->json('data.publication.submissions.data'), 'title');
        $this->assertEquals(['Quantum Entanglement'], $titles);
    }

    public function testSearchReviewerPrefixMatchesReviewersOnly(): void
    {
        ['publication' => $pub] = $this->seedPublicationForSearch();

        $response = $this->graphQL(self::PUB_SEARCH_QUERY, [
            'id' => (string)$pub->id,
            'search' => 'reviewer:bob',
        ]);

        $titles = array_column($response->json('data.publication.submissions.data'), 'title');
        $this->assertEquals(['Photosynthesis'], $titles);
    }

    public function testSearchCoordinatorPrefixMatchesCoordinatorsOnly(): void
    {
        ['publication' => $pub] = $this->seedPublicationForSearch();

        $response = $this->graphQL(self::PUB_SEARCH_QUERY, [
            'id' => (string)$pub->id,
            'search' => 'coordinator:carol',
        ]);

        $titles = array_column($response->json('data.publication.submissions.data'), 'title');
        $this->assertEquals(['Cold Fusion'], $titles);
    }

    public function testSearchUserPrefixMatchesAnyAssignedRole(): void
    {
        ['publication' => $pub] = $this->seedPublicationForSearch();

        // "alice" appears as a submitter but not as a reviewer or coordinator.
        $response = $this->graphQL(self::PUB_SEARCH_QUERY, [
            'id' => (string)$pub->id,
            'search' => 'user:alice',
        ]);

        $titles = array_column($response->json('data.publication.submissions.data'), 'title');
        $this->assertEquals(['Quantum Entanglement'], $titles);

        // "carol" is only a coordinator — the user: prefix should still find it.
        $response = $this->graphQL(self::PUB_SEARCH_QUERY, [
            'id' => (string)$pub->id,
            'search' => 'user:carol',
        ]);

        $titles = array_column($response->json('data.publication.submissions.data'), 'title');
        $this->assertEquals(['Cold Fusion'], $titles);
    }

    public function testSearchNoPrefixMatchesTitleOrAnyUser(): void
    {
        ['publication' => $pub] = $this->seedPublicationForSearch();

        // "alice" appears in one title AND as a submitter on a different row.
        $response = $this->graphQL(self::PUB_SEARCH_QUERY, [
            'id' => (string)$pub->id,
            'search' => 'alice',
        ]);

        $titles = array_column($response->json('data.publication.submissions.data'), 'title');
        sort($titles);
        $this->assertEquals(['A paper about Alice', 'Quantum Entanglement'], $titles);
    }

    public function testSearchBelowMinimumLengthIsIgnored(): void
    {
        ['publication' => $pub] = $this->seedPublicationForSearch();

        // Two characters — should be treated as if no search was provided.
        $response = $this->graphQL(self::PUB_SEARCH_QUERY, [
            'id' => (string)$pub->id,
            'search' => 'al',
        ]);

        $this->assertEquals(5, $response->json('data.publication.submissions.paginatorInfo.total'));

        // Minimum applies to the post-prefix term too.
        $response = $this->graphQL(self::PUB_SEARCH_QUERY, [
            'id' => (string)$pub->id,
            'search' => 'title:al',
        ]);

        $this->assertEquals(5, $response->json('data.publication.submissions.paginatorInfo.total'));
    }

    public function testSearchEscapesLikeWildcards(): void
    {
        ['editor' => $editor, 'publication' => $pub] = $this->seedPublicationForSearch();
        $this->actingAs($editor);

        // Adding a row with a literal "%" in its title. A naive LIKE would
        // match every row when the user searches for "%"; with escaping
        // it should match only this one.
        $submitter = User::factory()->create();
        Submission::factory()
            ->for($pub)
            ->hasAttached($submitter, [], 'submitters')
            ->create(['title' => 'Literal 100% match', 'status' => Submission::INITIALLY_SUBMITTED]);

        $response = $this->graphQL(self::PUB_SEARCH_QUERY, [
            'id' => (string)$pub->id,
            'search' => 'title:100%',
        ]);

        $titles = array_column($response->json('data.publication.submissions.data'), 'title');
        $this->assertEquals(['Literal 100% match'], $titles);

        // Same for underscore.
        Submission::factory()
            ->for($pub)
            ->hasAttached($submitter, [], 'submitters')
            ->create(['title' => 'snake_case title', 'status' => Submission::INITIALLY_SUBMITTED]);

        $response = $this->graphQL(self::PUB_SEARCH_QUERY, [
            'id' => (string)$pub->id,
            'search' => 'title:snake_case',
        ]);

        $titles = array_column($response->json('data.publication.submissions.data'), 'title');
        $this->assertEquals(['snake_case title'], $titles);
    }

    public function testSearchExcludesDraftSubmissions(): void
    {
        ['publication' => $pub] = $this->seedPublicationForSearch();

        // Add a draft whose title matches the search term — it should
        // still be hidden from the publication dashboard.
        $submitter = User::factory()->create();
        Submission::factory()
            ->for($pub)
            ->hasAttached($submitter, [], 'submitters')
            ->create(['title' => 'Draft about Alice', 'status' => Submission::DRAFT]);

        $response = $this->graphQL(self::PUB_SEARCH_QUERY, [
            'id' => (string)$pub->id,
            'search' => 'title:Alice',
        ]);

        $titles = array_column($response->json('data.publication.submissions.data'), 'title');
        $this->assertEquals(['A paper about Alice'], $titles);
    }
}
