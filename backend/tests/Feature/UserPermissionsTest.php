<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        foreach (Role::SLUG_TO_TITLE as $slug => $title) {
            $role = Role::where('name', $slug)->first();
            $this->assertNotNull($role, $slug);
            $this->assertEquals($title, $role->title);
        }
    }

    /**
     * @return void
     */
    public function testAssignmentOfApplicationAdministratorRoleToUser()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::APPLICATION_ADMINISTRATOR);

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
