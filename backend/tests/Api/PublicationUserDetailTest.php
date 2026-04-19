<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class PublicationUserDetailTest extends ApiTestCase
{
    use RefreshDatabase;

    private const USER_QUERY = '
        query ($publicationId: ID!, $userId: ID!) {
            publication(id: $publicationId) {
                id
                user(id: $userId) {
                    id
                    user { id name email }
                    as_submitter_count
                    as_reviewer_active_count
                    as_reviewer_completed_count
                    as_coordinator_active_count
                    as_coordinator_completed_count
                }
            }
        }
    ';

    private const USER_SUBMISSIONS_QUERY = '
        query (
            $publicationId: ID!,
            $userId: ID!,
            $roles: [SubmissionUserRoles!],
            $phase: SubmissionPhase
        ) {
            publication(id: $publicationId) {
                user(id: $userId) {
                    submissions(first: 25, roles: $roles, phase: $phase) {
                        paginatorInfo { total }
                        data {
                            id
                            title
                            status
                        }
                    }
                }
            }
        }
    ';

    /**
     * Create an editor + publication with one user who holds multiple
     * roles across a mix of active and completed submissions.
     *
     * @return array{editor: User, publication: Publication, dave: User}
     */
    private function seedMixedRoleUser(): array
    {
        /** @var User $editor */
        $editor = User::factory()->create();
        $this->actingAs($editor);

        $publication = Publication::factory()
            ->hasAttached($editor, [], 'editors')
            ->create();

        /** @var User $dave */
        $dave = User::factory()->create(['name' => 'Dave Dual']);

        // Dave reviews 1 active, 1 completed; coordinates 1 active.
        Submission::factory()
            ->for($publication)
            ->hasAttached(User::factory()->create(), [], 'submitters')
            ->hasAttached($dave, [], 'reviewers')
            ->create([
                'title' => 'Review Active',
                'status' => Submission::UNDER_REVIEW,
            ]);
        Submission::factory()
            ->for($publication)
            ->hasAttached(User::factory()->create(), [], 'submitters')
            ->hasAttached($dave, [], 'reviewers')
            ->create([
                'title' => 'Review Completed',
                'status' => Submission::ACCEPTED_AS_FINAL,
            ]);
        Submission::factory()
            ->for($publication)
            ->hasAttached(User::factory()->create(), [], 'submitters')
            ->hasAttached($dave, [], 'reviewCoordinators')
            ->create([
                'title' => 'Coordinate Active',
                'status' => Submission::AWAITING_DECISION,
            ]);

        return compact('editor', 'publication', 'dave');
    }

    public function testReturnsCountsPerRoleAndPhase(): void
    {
        $seed = $this->seedMixedRoleUser();

        $response = $this->graphQL(self::USER_QUERY, [
            'publicationId' => (string)$seed['publication']->id,
            'userId' => (string)$seed['dave']->id,
        ]);

        $user = $response->json('data.publication.user');
        $this->assertSame((string)$seed['dave']->id, $user['id']);
        $this->assertSame(0, $user['as_submitter_count']);
        $this->assertSame(1, $user['as_reviewer_active_count']);
        $this->assertSame(1, $user['as_reviewer_completed_count']);
        $this->assertSame(1, $user['as_coordinator_active_count']);
        $this->assertSame(0, $user['as_coordinator_completed_count']);
    }

    public function testReturnsNullForUserWithNoActivity(): void
    {
        $seed = $this->seedMixedRoleUser();
        $outsider = User::factory()->create();

        $response = $this->graphQL(self::USER_QUERY, [
            'publicationId' => (string)$seed['publication']->id,
            'userId' => (string)$outsider->id,
        ]);

        $this->assertNull($response->json('data.publication.user'));
    }

    public function testSubmissionsFilterByRole(): void
    {
        $seed = $this->seedMixedRoleUser();

        $response = $this->graphQL(self::USER_SUBMISSIONS_QUERY, [
            'publicationId' => (string)$seed['publication']->id,
            'userId' => (string)$seed['dave']->id,
            'roles' => ['reviewer'],
        ]);

        $titles = array_column(
            $response->json('data.publication.user.submissions.data'),
            'title'
        );
        sort($titles);
        $this->assertSame(['Review Active', 'Review Completed'], $titles);
    }

    public function testSubmissionsFilterByPhase(): void
    {
        $seed = $this->seedMixedRoleUser();

        $response = $this->graphQL(self::USER_SUBMISSIONS_QUERY, [
            'publicationId' => (string)$seed['publication']->id,
            'userId' => (string)$seed['dave']->id,
            'phase' => 'active',
        ]);

        $titles = array_column(
            $response->json('data.publication.user.submissions.data'),
            'title'
        );
        sort($titles);
        $this->assertSame(['Coordinate Active', 'Review Active'], $titles);
    }

    public function testSubmissionsFilterByRoleAndPhase(): void
    {
        $seed = $this->seedMixedRoleUser();

        $response = $this->graphQL(self::USER_SUBMISSIONS_QUERY, [
            'publicationId' => (string)$seed['publication']->id,
            'userId' => (string)$seed['dave']->id,
            'roles' => ['reviewer'],
            'phase' => 'completed',
        ]);

        $titles = array_column(
            $response->json('data.publication.user.submissions.data'),
            'title'
        );
        $this->assertSame(['Review Completed'], $titles);
    }

    public function testSubmissionsDoNotIncludeDrafts(): void
    {
        $seed = $this->seedMixedRoleUser();

        Submission::factory()
            ->for($seed['publication'])
            ->hasAttached(User::factory()->create(), [], 'submitters')
            ->hasAttached($seed['dave'], [], 'reviewers')
            ->create([
                'title' => 'Hidden Draft',
                'status' => Submission::DRAFT,
            ]);

        $response = $this->graphQL(self::USER_SUBMISSIONS_QUERY, [
            'publicationId' => (string)$seed['publication']->id,
            'userId' => (string)$seed['dave']->id,
        ]);

        $titles = array_column(
            $response->json('data.publication.user.submissions.data'),
            'title'
        );
        $this->assertNotContains('Hidden Draft', $titles);
    }
}
