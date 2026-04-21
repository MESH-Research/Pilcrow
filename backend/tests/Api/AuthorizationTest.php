<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

/**
 * Regression guard for root-query authorization.
 *
 * These tests lock in that:
 *   - Unauthenticated callers cannot reach authenticated root queries.
 *   - Non-admin authenticated callers cannot reach admin-only root queries.
 *   - The `User` type does not leak cross-user data via relations (raw_submissions).
 *
 * If a future change relaxes one of these gates, the failing test should force
 * a deliberate decision rather than a silent exposure.
 */
class AuthorizationTest extends ApiTestCase
{
    use RefreshDatabase;

    public function testGuestCannotListUsers(): void
    {
        User::factory()->count(3)->create();

        $response = $this->graphQL(
            'query { users(first: 10) { data { id email } } }'
        );

        $this->assertNotEmpty($response->json('errors'));
        $this->assertNull($response->json('data.users'));
    }

    public function testRegularUserCannotListUsers(): void
    {
        $this->actingAs(User::factory()->create());
        User::factory()->count(3)->create();

        $response = $this->graphQL(
            'query { users(first: 10) { data { id email } } }'
        );

        $response->assertJsonPath('errors.0.message', 'This action is unauthorized.');
    }

    public function testAdminCanListUsers(): void
    {
        $this->beAppAdmin();
        User::factory()->count(3)->create();

        $response = $this->graphQL(
            'query { users(first: 10) { data { id email } } }'
        );

        $this->assertNull($response->json('errors'));
        $this->assertNotEmpty($response->json('data.users.data'));
    }

    public function testGuestCannotQueryUserById(): void
    {
        $user = User::factory()->create();

        $response = $this->graphQL(
            'query ($id: ID!) { user(id: $id) { id email } }',
            ['id' => $user->id]
        );

        $this->assertNotEmpty($response->json('errors'));
        $this->assertNull($response->json('data.user'));
    }

    public function testRegularUserCannotQueryAnotherUserById(): void
    {
        $this->actingAs(User::factory()->create());
        $target = User::factory()->create();

        $response = $this->graphQL(
            'query ($id: ID!) { user(id: $id) { id email } }',
            ['id' => $target->id]
        );

        $response->assertJsonPath('errors.0.message', 'This action is unauthorized.');
    }

    public function testRegularUserCannotQueryThemselvesViaUserField(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->graphQL(
            'query ($id: ID!) { user(id: $id) { id email } }',
            ['id' => $user->id]
        );

        $response->assertJsonPath('errors.0.message', 'This action is unauthorized.');
    }

    public function testAdminCanQueryAnyUserById(): void
    {
        $this->beAppAdmin();
        $target = User::factory()->create(['email' => 'target@example.test']);

        $response = $this->graphQL(
            'query ($id: ID!) { user(id: $id) { id email } }',
            ['id' => $target->id]
        );

        $response->assertJsonPath('data.user.email', 'target@example.test');
    }

    public function testGuestCannotSearchUsers(): void
    {
        User::factory()->count(2)->create();

        $response = $this->graphQL(
            'query { userSearch { data { id email } } }'
        );

        $this->assertNotEmpty($response->json('errors'));
        $this->assertNull($response->json('data.userSearch'));
    }

    public function testAuthenticatedUserCanSearchUsers(): void
    {
        $this->actingAs(User::factory()->create());
        User::factory()->count(2)->create();

        $response = $this->graphQL(
            'query { userSearch { data { id } } }'
        );

        $this->assertNull($response->json('errors'));
    }

    public function testGuestCannotListPublications(): void
    {
        Publication::factory()->count(2)->create();

        $response = $this->graphQL(
            'query { publications(first: 10) { data { id name } } }'
        );

        $this->assertNotEmpty($response->json('errors'));
        $this->assertNull($response->json('data.publications'));
    }

    public function testGuestCannotListSubmissions(): void
    {
        Submission::factory()
            ->for(Publication::factory()->create())
            ->hasAttached(User::factory()->create(), [], 'submitters')
            ->create();

        $response = $this->graphQL(
            'query { submissions(first: 10) { data { id title } } }'
        );

        $this->assertNotEmpty($response->json('errors'));
        $this->assertNull($response->json('data.submissions'));
    }

    /**
     * `raw_submissions` is a deprecated field that previously used `@all`, which
     * would have returned every submission in the database regardless of the
     * parent user. This locks in that it is scoped to the parent user.
     */
    public function testRawSubmissionsIsScopedToTheParentUser(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $publication = Publication::factory()->create();

        Submission::factory()
            ->for($publication)
            ->hasAttached($me, [], 'submitters')
            ->create(['title' => 'Mine']);

        Submission::factory()
            ->for($publication)
            ->hasAttached($other, [], 'submitters')
            ->create(['title' => 'Not mine']);

        $this->actingAs($me);

        $response = $this->graphQL(
            'query { currentUser { raw_submissions { id title } } }'
        );

        $titles = collect($response->json('data.currentUser.raw_submissions'))
            ->pluck('title')
            ->all();

        $this->assertEquals(['Mine'], $titles);
    }
}
