<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Auth\Roles\ScopedRole;
use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Direct coverage for SubmissionBuilder scopes that are not yet wired into the
 * GraphQL schema (roleFilter, visible). They are exercised here against the
 * query builder so the behaviour is locked in ahead of the publication-admin
 * work that will consume them.
 */
class SubmissionBuilderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * roleFilter restricts submissions to those with an assignment in any of
     * the given roles, regardless of which user holds the assignment.
     *
     * @return void
     */
    public function test_role_filter_matches_submissions_with_assignment_in_role(): void
    {
        $user = User::factory()->create();
        $publication = Publication::factory()->create();

        $reviewed = Submission::factory()
            ->for($publication)
            ->hasAttached($user, [], 'reviewers')
            ->create();

        Submission::factory()
            ->for($publication)
            ->hasAttached($user, [], 'submitters')
            ->create();

        $results = Submission::query()
            ->roleFilter([ScopedRole::Reviewer->toSlug()])
            ->get();

        $this->assertEquals([$reviewed->id], $results->pluck('id')->all());
    }

    /**
     * roleFilter accepts multiple roles and returns the union of matches.
     *
     * @return void
     */
    public function test_role_filter_accepts_multiple_roles(): void
    {
        $user = User::factory()->create();
        $publication = Publication::factory()->create();

        $reviewed = Submission::factory()
            ->for($publication)
            ->hasAttached($user, [], 'reviewers')
            ->create();

        $coordinated = Submission::factory()
            ->for($publication)
            ->hasAttached($user, [], 'reviewCoordinators')
            ->create();

        Submission::factory()
            ->for($publication)
            ->hasAttached($user, [], 'submitters')
            ->create();

        $results = Submission::query()
            ->roleFilter([
                ScopedRole::Reviewer->toSlug(),
                ScopedRole::ReviewCoordinator->toSlug(),
            ])
            ->get();

        $this->assertEqualsCanonicalizing(
            [$reviewed->id, $coordinated->id],
            $results->pluck('id')->all()
        );
    }

    /**
     * visible includes submissions in a publication the authenticated user
     * belongs to, even without a direct submission assignment.
     *
     * @return void
     */
    public function test_visible_includes_submissions_in_users_publication(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $publication = Publication::factory()
            ->hasAttached($user, [], 'editors')
            ->create();
        $submission = Submission::factory()->for($publication)->create();

        $results = Submission::query()->visible()->get();

        $this->assertEquals([$submission->id], $results->pluck('id')->all());
    }

    /**
     * visible includes submissions the authenticated user is directly assigned
     * to, even in a publication they do not belong to.
     *
     * @return void
     */
    public function test_visible_includes_submissions_user_is_assigned_to(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $publication = Publication::factory()->create();
        $submission = Submission::factory()
            ->for($publication)
            ->hasAttached($user, [], 'reviewers')
            ->create();

        $results = Submission::query()->visible()->get();

        $this->assertEquals([$submission->id], $results->pluck('id')->all());
    }

    /**
     * visible excludes submissions the authenticated user has no relationship
     * to via publication membership or assignment.
     *
     * @return void
     */
    public function test_visible_excludes_unrelated_submissions(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Submission::factory()
            ->for(Publication::factory()->create())
            ->create();

        $this->assertCount(0, Submission::query()->visible()->get());
    }
}
