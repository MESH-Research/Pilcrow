<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Auth\ScopedRole;
use App\Models\Publication;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * Characterization tests for PublicationPolicy.
 *
 * These lock in the CURRENT (Spatie + custom pivot) behavior so the planned
 * RBAC -> ABAC / Bouncer migration cannot silently change authorization.
 * They assert what the code does today, not what it ideally should do.
 */
class PublicationPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function attachToPublication(User $user, Publication $publication, int $roleId): void
    {
        $user->publications()->attach($publication->id, ['role_id' => $roleId]);
    }

    private function appAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::APPLICATION_ADMINISTRATOR);

        return $admin;
    }

    // ---- create -------------------------------------------------------------

    public function testCreateAllowsApplicationAdministrator(): void
    {
        $this->assertTrue($this->appAdmin()->can('create', Publication::class));
    }

    public function testCreateDeniesPublicationAdministrator(): void
    {
        $user = User::factory()->create();
        $this->attachToPublication($user, Publication::factory()->create(), (int)ScopedRole::PublicationAdmin->pivotValue());

        $this->assertFalse($user->can('create', Publication::class));
    }

    public function testCreateDeniesUnaffiliatedUser(): void
    {
        $this->assertFalse(User::factory()->create()->can('create', Publication::class));
    }

    // ---- update -------------------------------------------------------------

    public function testUpdateAllowsApplicationAdministrator(): void
    {
        $publication = Publication::factory()->create();

        $this->assertTrue($this->appAdmin()->can('update', [Publication::class, ['id' => $publication->id]]));
    }

    public function testUpdateAllowsPublicationAdministratorOfThatPublication(): void
    {
        $publication = Publication::factory()->create();
        $user = User::factory()->create();
        $this->attachToPublication($user, $publication, (int)ScopedRole::PublicationAdmin->pivotValue());

        $this->assertTrue($user->can('update', [Publication::class, ['id' => $publication->id]]));
    }

    public function testUpdateDeniesPublicationAdministratorOfDifferentPublication(): void
    {
        $user = User::factory()->create();
        $this->attachToPublication($user, Publication::factory()->create(), (int)ScopedRole::PublicationAdmin->pivotValue());

        $other = Publication::factory()->create();
        $this->assertFalse($user->can('update', [Publication::class, ['id' => $other->id]]));
    }

    public function testUpdateDeniesEditor(): void
    {
        $publication = Publication::factory()->create();
        $user = User::factory()->create();
        $this->attachToPublication($user, $publication, (int)ScopedRole::Editor->pivotValue());

        $this->assertFalse($user->can('update', [Publication::class, ['id' => $publication->id]]));
    }

    public function testUpdateDeniesUnaffiliatedUser(): void
    {
        $publication = Publication::factory()->create();

        $this->assertFalse(User::factory()->create()->can('update', [Publication::class, ['id' => $publication->id]]));
    }

    // ---- view ---------------------------------------------------------------

    public function testViewAllowsAnyoneForPubliclyVisiblePublication(): void
    {
        $publication = Publication::factory()->create(['is_publicly_visible' => true]);

        // Guest (no user) is allowed when publicly visible.
        $this->assertTrue(Gate::forUser(null)->check('view', $publication));
        $this->assertTrue(User::factory()->create()->can('view', $publication));
    }

    public function testViewDeniesGuestForHiddenPublication(): void
    {
        $publication = Publication::factory()->create(['is_publicly_visible' => false]);

        $this->assertFalse(Gate::forUser(null)->check('view', $publication));
    }

    public function testViewAllowsApplicationAdministratorForHiddenPublication(): void
    {
        $publication = Publication::factory()->create(['is_publicly_visible' => false]);

        $this->assertTrue($this->appAdmin()->can('view', $publication));
    }

    public function testViewAllowsAnyPublicationRoleForHiddenPublication(): void
    {
        $publication = Publication::factory()->create(['is_publicly_visible' => false]);
        $editor = User::factory()->create();
        $this->attachToPublication($editor, $publication, (int)ScopedRole::Editor->pivotValue());

        $this->assertTrue($editor->can('view', $publication));
    }

    public function testViewDeniesUnaffiliatedUserForHiddenPublication(): void
    {
        $publication = Publication::factory()->create(['is_publicly_visible' => false]);

        $this->assertFalse(User::factory()->create()->can('view', $publication));
    }

    public function testViewDeniesRoleHolderOfDifferentPublicationForHiddenPublication(): void
    {
        $hidden = Publication::factory()->create(['is_publicly_visible' => false]);
        $user = User::factory()->create();
        $this->attachToPublication($user, Publication::factory()->create(), (int)ScopedRole::Editor->pivotValue());

        $this->assertFalse($user->can('view', $hidden));
    }
}
