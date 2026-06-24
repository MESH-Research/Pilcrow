<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Auth\Roles\GlobalRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Tests\TestCase;

/**
 * Coverage for the global application-administrator role now that
 * authorization is handled by Bouncer (the ABAC ability registry seeded by
 * AbacSeeder) rather than spatie/laravel-permission.
 */
class UserPermissionsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testAllRoleSlugsAreSeededWithTitles()
    {
        foreach (GlobalRole::cases() as $globalRole) {
            $role = Bouncer::role()->where('name', $globalRole->toSlug())->first();
            $this->assertNotNull($role, $globalRole->toSlug());
            $this->assertEquals($globalRole->title(), $role->title);
        }
    }

    /**
     * @return void
     */
    public function testAssignmentOfApplicationAdministratorRoleToUser()
    {
        $user = User::factory()->create();
        $user->assignRole(GlobalRole::ApplicationAdministrator);

        $this->assertTrue($user->isApplicationAdministrator());
    }

    /**
     * @return void
     */
    public function testUnaffiliatedUserIsNotApplicationAdministrator()
    {
        $this->assertFalse(User::factory()->create()->isApplicationAdministrator());
    }
}
