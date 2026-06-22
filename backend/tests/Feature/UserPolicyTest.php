<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Auth\ScopedRole;
use App\Models\Publication;
use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function attachToPublication(User $user, Publication $publication, int $roleId): void
    {
        $user->publications()->attach($publication->id, ['role_id' => $roleId]);
    }

    public function testViewEmailAllowsSelf(): void
    {
        $user = User::factory()->create();
        $this->assertTrue($user->can('viewEmail', $user));
    }

    public function testViewEmailAllowsApplicationAdministrator(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $target = User::factory()->create();

        $this->assertTrue($admin->can('viewEmail', $target));
    }

    public function testViewEmailAllowsPublicationAdminForUserInSamePublication(): void
    {
        $publication = Publication::factory()->create();
        $admin = User::factory()->create();
        $target = User::factory()->create();

        $this->attachToPublication($admin, $publication, (int)ScopedRole::PublicationAdmin->pivotValue());
        $this->attachToPublication($target, $publication, (int)ScopedRole::Editor->pivotValue());

        $this->assertTrue($admin->can('viewEmail', $target));
    }

    public function testViewEmailAllowsEditorForUserInSamePublication(): void
    {
        $publication = Publication::factory()->create();
        $editor = User::factory()->create();
        $target = User::factory()->create();

        $this->attachToPublication($editor, $publication, (int)ScopedRole::Editor->pivotValue());
        $this->attachToPublication($target, $publication, (int)ScopedRole::Editor->pivotValue());

        $this->assertTrue($editor->can('viewEmail', $target));
    }

    public function testViewEmailAllowsEditorForSubmitterInTheirPublication(): void
    {
        $publication = Publication::factory()->create();
        $editor = User::factory()->create();
        $this->attachToPublication($editor, $publication, (int)ScopedRole::Editor->pivotValue());

        $submitter = User::factory()->create();
        Submission::factory()
            ->for($publication)
            ->hasAttached($submitter, [], 'submitters')
            ->create();

        $this->assertTrue($editor->can('viewEmail', $submitter));
    }

    public function testViewEmailDeniesReviewer(): void
    {
        $publication = Publication::factory()->create();
        $reviewer = User::factory()->create();
        $coAuthor = User::factory()->create();

        Submission::factory()
            ->for($publication)
            ->hasAttached($reviewer, [], 'reviewers')
            ->hasAttached($coAuthor, [], 'submitters')
            ->create();

        $this->assertFalse($reviewer->can('viewEmail', $coAuthor));
    }

    public function testViewEmailDeniesReviewCoordinator(): void
    {
        $publication = Publication::factory()->create();
        $coordinator = User::factory()->create();
        $other = User::factory()->create();

        Submission::factory()
            ->for($publication)
            ->hasAttached($coordinator, [], 'reviewCoordinators')
            ->hasAttached($other, [], 'submitters')
            ->create();

        $this->assertFalse($coordinator->can('viewEmail', $other));
    }

    public function testViewEmailDeniesEditorOfDifferentPublication(): void
    {
        $editor = User::factory()->create();
        $this->attachToPublication($editor, Publication::factory()->create(), (int)ScopedRole::Editor->pivotValue());

        $other = User::factory()->create();
        $this->attachToPublication($other, Publication::factory()->create(), (int)ScopedRole::Editor->pivotValue());

        $this->assertFalse($editor->can('viewEmail', $other));
    }

    public function testViewAllowsOnlyApplicationAdministrator(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $other = User::factory()->create();

        $this->assertTrue($admin->can('view', $other));
        $this->assertFalse($other->can('view', $other));
        $this->assertFalse($other->can('view', $admin));
    }

    public function testViewEmailDeniesUnaffiliatedUser(): void
    {
        $viewer = User::factory()->create();
        $target = User::factory()->create();

        $this->assertFalse($viewer->can('viewEmail', $target));
    }

    public function testViewEmailMemoizesViewerPublicationLookup(): void
    {
        $publication = Publication::factory()->create();
        $editor = User::factory()->create();
        $this->attachToPublication($editor, $publication, (int)ScopedRole::Editor->pivotValue());

        $targets = User::factory()->count(5)->create();
        foreach ($targets as $target) {
            $this->attachToPublication($target, $publication, (int)ScopedRole::Editor->pivotValue());
        }

        // Prime the application-admin role lookup so it doesn't pollute the count.
        $editor->isApplicationAdministrator();

        DB::enableQueryLog();
        foreach ($targets as $target) {
            $this->assertTrue($editor->can('viewEmail', $target));
        }
        $queries = collect(DB::getQueryLog())->pluck('query');
        DB::disableQueryLog();

        // Viewer's privileged-publication lookup hits the publication_user
        // pivot exactly once even though the policy was invoked 5 times.
        $viewerPubLookups = $queries->filter(
            fn(string $sql) => str_contains($sql, 'publication_user')
                && str_contains($sql, 'role')
        );
        $this->assertCount(1, $viewerPubLookups);
    }
}
