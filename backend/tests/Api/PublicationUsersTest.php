<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class PublicationUsersTest extends ApiTestCase
{
    use RefreshDatabase;

    private const USERS_QUERY = '
        query (
            $id: ID!,
            $roles: [SubmissionUserRoles!]!,
            $search: String,
            $staged: Boolean
        ) {
            publication(id: $id) {
                users(
                    first: 25,
                    roles: $roles,
                    search: $search,
                    staged: $staged
                ) {
                    paginatorInfo { total }
                    data {
                        id
                        user {
                            id
                            name
                            email
                            staged
                        }
                        as_submitter_count
                        as_reviewer_active_count
                        as_reviewer_completed_count
                        as_coordinator_active_count
                        as_coordinator_completed_count
                    }
                }
            }
        }
    ';

    /**
     * Seed a publication with users in distinct roles so tab-based
     * filters can be verified independently.
     *
     * @return array{editor: User, publication: Publication, alice: User, bob: User, carol: User, dave: User}
     */
    private function seedPublicationWithRoles(): array
    {
        /** @var User $editor */
        $editor = User::factory()->create();
        $this->actingAs($editor);

        $publication = Publication::factory()
            ->hasAttached($editor, [], 'editors')
            ->create();

        $alice = User::factory()->create(['name' => 'Alice Submitter']);
        $bob = User::factory()->create(['name' => 'Bob Reviewer']);
        $carol = User::factory()->create(['name' => 'Carol Coordinator']);
        $dave = User::factory()->create(['name' => 'Dave Dual']);

        // Alice submits two non-draft submissions and one draft
        foreach (['Submission One', 'Submission Two'] as $title) {
            Submission::factory()
                ->for($publication)
                ->hasAttached($alice, [], 'submitters')
                ->create(['title' => $title, 'status' => Submission::INITIALLY_SUBMITTED]);
        }
        Submission::factory()
            ->for($publication)
            ->hasAttached($alice, [], 'submitters')
            ->create(['title' => 'Hidden Draft', 'status' => Submission::DRAFT]);

        // Bob is a reviewer on three submissions
        foreach (['Review A', 'Review B', 'Review C'] as $title) {
            Submission::factory()
                ->for($publication)
                ->hasAttached(User::factory()->create(), [], 'submitters')
                ->hasAttached($bob, [], 'reviewers')
                ->create(['title' => $title, 'status' => Submission::UNDER_REVIEW]);
        }

        // Carol coordinates two submissions
        foreach (['Coord One', 'Coord Two'] as $title) {
            Submission::factory()
                ->for($publication)
                ->hasAttached(User::factory()->create(), [], 'submitters')
                ->hasAttached($carol, [], 'reviewCoordinators')
                ->create(['title' => $title, 'status' => Submission::AWAITING_DECISION]);
        }

        // Dave is both reviewer and coordinator across different submissions
        Submission::factory()
            ->for($publication)
            ->hasAttached(User::factory()->create(), [], 'submitters')
            ->hasAttached($dave, [], 'reviewers')
            ->create(['title' => 'Dave Reviews', 'status' => Submission::UNDER_REVIEW]);
        Submission::factory()
            ->for($publication)
            ->hasAttached(User::factory()->create(), [], 'submitters')
            ->hasAttached($dave, [], 'reviewCoordinators')
            ->create(['title' => 'Dave Coordinates', 'status' => Submission::AWAITING_DECISION]);

        return compact('editor', 'publication', 'alice', 'bob', 'carol', 'dave');
    }

    public function testSubmitterTabReturnsOnlySubmitters(): void
    {
        $seed = $this->seedPublicationWithRoles();

        $response = $this->graphQL(self::USERS_QUERY, [
            'id' => (string)$seed['publication']->id,
            'roles' => ['submitter'],
        ]);

        $data = $response->json('data.publication.users.data');
        $names = array_map(fn($row) => $row['user']['name'], $data);

        // Alice is an explicit submitter; the factory-created submitters
        // (used as "real" submitters for Bob/Carol/Dave's submissions)
        // should also show up, since they're genuine submitters too.
        $this->assertContains('Alice Submitter', $names);
        $this->assertNotContains('Bob Reviewer', $names);
        $this->assertNotContains('Carol Coordinator', $names);
        $this->assertNotContains('Dave Dual', $names);
    }

    public function testTeamTabReturnsReviewersAndCoordinators(): void
    {
        $seed = $this->seedPublicationWithRoles();

        $response = $this->graphQL(self::USERS_QUERY, [
            'id' => (string)$seed['publication']->id,
            'roles' => ['reviewer', 'review_coordinator'],
        ]);

        $data = $response->json('data.publication.users.data');
        $names = array_map(fn($row) => $row['user']['name'], $data);

        $this->assertContains('Bob Reviewer', $names);
        $this->assertContains('Carol Coordinator', $names);
        $this->assertContains('Dave Dual', $names);
        $this->assertNotContains('Alice Submitter', $names);
    }

    public function testUsersAreDeduplicated(): void
    {
        $seed = $this->seedPublicationWithRoles();

        // Alice submitted 2 non-draft + 1 draft — should appear ONCE
        // with as_submitter_count = 2.
        $response = $this->graphQL(self::USERS_QUERY, [
            'id' => (string)$seed['publication']->id,
            'roles' => ['submitter'],
            'search' => 'Alice',
        ]);

        $data = $response->json('data.publication.users.data');
        $this->assertCount(1, $data);
        $this->assertSame('Alice Submitter', $data[0]['user']['name']);
        $this->assertSame(2, $data[0]['as_submitter_count']);
    }

    public function testRoleCountsReflectOnlyNonDraftSubmissions(): void
    {
        $seed = $this->seedPublicationWithRoles();

        $response = $this->graphQL(self::USERS_QUERY, [
            'id' => (string)$seed['publication']->id,
            'roles' => ['submitter', 'reviewer', 'review_coordinator'],
            'search' => 'Dave',
        ]);

        $data = $response->json('data.publication.users.data');
        $this->assertCount(1, $data);
        $dave = $data[0];
        // Dave reviews 1, coordinates 1, submits 0 here. Both are
        // active submissions (UNDER_REVIEW + AWAITING_DECISION).
        $this->assertSame(0, $dave['as_submitter_count']);
        $this->assertSame(1, $dave['as_reviewer_active_count']);
        $this->assertSame(0, $dave['as_reviewer_completed_count']);
        $this->assertSame(1, $dave['as_coordinator_active_count']);
        $this->assertSame(0, $dave['as_coordinator_completed_count']);
    }

    public function testSearchMatchesUserFields(): void
    {
        $seed = $this->seedPublicationWithRoles();

        $response = $this->graphQL(self::USERS_QUERY, [
            'id' => (string)$seed['publication']->id,
            'roles' => ['reviewer', 'review_coordinator'],
            'search' => 'Carol',
        ]);

        $names = array_map(
            fn($row) => $row['user']['name'],
            $response->json('data.publication.users.data')
        );
        $this->assertSame(['Carol Coordinator'], $names);
    }

    public function testBelowMinimumSearchLengthIsIgnored(): void
    {
        $seed = $this->seedPublicationWithRoles();

        $response = $this->graphQL(self::USERS_QUERY, [
            'id' => (string)$seed['publication']->id,
            'roles' => ['reviewer', 'review_coordinator'],
            'search' => 'ca',
        ]);

        // Two-char search is a no-op; all team members return.
        $names = array_map(
            fn($row) => $row['user']['name'],
            $response->json('data.publication.users.data')
        );
        $this->assertContains('Bob Reviewer', $names);
        $this->assertContains('Carol Coordinator', $names);
        $this->assertContains('Dave Dual', $names);
    }

    public function testRoleCountsSplitActiveAndCompletedSubmissions(): void
    {
        $seed = $this->seedPublicationWithRoles();
        $bob = $seed['bob'];

        // Bob already reviews 3 active submissions (UNDER_REVIEW).
        // Add one accepted (completed) and one rejected (completed).
        Submission::factory()
            ->for($seed['publication'])
            ->hasAttached(User::factory()->create(), [], 'submitters')
            ->hasAttached($bob, [], 'reviewers')
            ->create([
                'title' => 'Review Accepted',
                'status' => Submission::ACCEPTED_AS_FINAL,
            ]);
        Submission::factory()
            ->for($seed['publication'])
            ->hasAttached(User::factory()->create(), [], 'submitters')
            ->hasAttached($bob, [], 'reviewers')
            ->create([
                'title' => 'Review Rejected',
                'status' => Submission::REJECTED,
            ]);

        $response = $this->graphQL(self::USERS_QUERY, [
            'id' => (string)$seed['publication']->id,
            'roles' => ['reviewer', 'review_coordinator'],
            'search' => 'Bob',
        ]);

        $data = $response->json('data.publication.users.data');
        $this->assertCount(1, $data);
        $bobRow = $data[0];
        $this->assertSame(3, $bobRow['as_reviewer_active_count']);
        $this->assertSame(2, $bobRow['as_reviewer_completed_count']);
    }

    public function testStagedFilterReturnsOnlyInvitedUsers(): void
    {
        $seed = $this->seedPublicationWithRoles();

        $invited = User::createStagedUser('invited@example.com');
        Submission::factory()
            ->for($seed['publication'])
            ->hasAttached(User::factory()->create(), [], 'submitters')
            ->hasAttached($invited, [], 'reviewers')
            ->create([
                'title' => 'Invited Review',
                'status' => Submission::UNDER_REVIEW,
            ]);

        $response = $this->graphQL(self::USERS_QUERY, [
            'id' => (string)$seed['publication']->id,
            'roles' => ['reviewer', 'review_coordinator'],
            'staged' => true,
        ]);

        $data = $response->json('data.publication.users.data');
        $emails = array_map(fn($row) => $row['user']['email'], $data);
        $this->assertSame(['invited@example.com'], $emails);
    }

    public function testOutsiderCannotQueryHiddenPublicationUsers(): void
    {
        $owner = User::factory()->create();
        $publication = Publication::factory()
            ->hidden()
            ->hasAttached($owner, [], 'publicationAdmins')
            ->create();

        Submission::factory()
            ->for($publication)
            ->hasAttached(User::factory()->create(), [], 'submitters')
            ->create(['status' => Submission::INITIALLY_SUBMITTED]);

        /** @var User $outsider */
        $outsider = User::factory()->create();
        $this->actingAs($outsider);

        $response = $this->graphQL(self::USERS_QUERY, [
            'id' => (string)$publication->id,
            'roles' => ['submitter'],
        ]);

        $response->assertJsonPath('data.publication', null);
    }
}
