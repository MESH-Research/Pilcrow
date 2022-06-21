<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPermissionsTest extends TestCase
{
    use RefreshDatabase;

    private $test_permission = 'test permission';
    private $test_user_role = 'Test User Role';

    /**
     * @return void
     */
    public function testCreationOfRandomRole()
    {
        $role = Role::factory()->create();
        $this->assertNotNull($role->name);
        $this->assertNotEmpty($role->name);
        $this->assertIsString($role->name);
    }

    /**
     * @return void
     */
    public function testCreationOfExplicitlyNamedTestUserRole()
    {
        $role = Role::factory()->create(['name' => $this->test_permission]);
        $this->assertEquals($role->name, $this->test_permission);
    }

    /**
     * @return void
     */
    public function testThatUserRoleRecordsExist()
    {
        $roles = Role::getArrayOfAllRoleNames();
        foreach ($roles as $role) {
            $record = Role::where('name', $role)->get();
            $this->assertTrue($record->count() > 0);
        }
    }

    /**
     * @return void
     */
    public function testAssignmentOfApplicationAdministratorRoleToUser()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->assertTrue($user->hasRole(Role::APPLICATION_ADMINISTRATOR));
    }

    /**
     * @return void
     */
    public function testCreationOfRandomPermission()
    {
        $permission = Permission::factory()->create();
        $this->assertNotNull($permission->name);
        $this->assertNotEmpty($permission->name);
        $this->assertIsString($permission->name);
    }

    /**
     * @return void
     */
    public function testCreationOfTestPermission()
    {
        $permission = Permission::factory()->create(['name' => $this->test_permission]);
        $this->assertEquals($permission->name, $this->test_permission);
    }

    /**
     * @return void
     */
    public function testAssignmentOfTestPermissionToApplicationAdministratorRole()
    {
        $permission = Permission::factory()->create(['name' => $this->test_permission]);
        $role = Role::findByName(Role::APPLICATION_ADMINISTRATOR);
        $permission->assignRole($role->name);
        $this->assertTrue($role->hasPermissionTo($this->test_permission));
    }

    /**
     * @return void
     */
    public function testUserHasTestPermissionByAssignedRole()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create(['name' => $this->test_permission]);
        $role = Role::findByName(Role::APPLICATION_ADMINISTRATOR);
        $permission->assignRole($role->name);
        $user->assignRole($role->name);
        $this->assertTrue($user->can($this->test_permission));
    }

    /**
     * @return void
     */
    public function testUserDoesNotHaveTestPermissionByAssignedRole()
    {
        $user = User::factory()->create();
        Permission::factory()->create(['name' => $this->test_permission]);
        $test_role = Role::factory()->create(['name' => $this->test_user_role]);
        $test_role->givePermissionTo($this->test_permission);
        $user->assignRole(Role::PUBLICATION_ADMINISTRATOR);
        $this->assertFalse($user->can($this->test_permission));
    }

    /**
     * @return void
     */
    public function testPermissionToUpdateUsersExists()
    {
        /** @var Permission $permission */
        $permissions = Permission::where('name', Permission::UPDATE_USERS)->get();
        $this->assertNotNull($permissions);
        $this->assertEquals(1, $permissions->count());
        $this->assertEquals($permissions->first()->name, Permission::UPDATE_USERS);
    }

    /**
     * @return void
     */
    public function testUserCanUpdateUsersAsAnApplicationAdministrator()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->assertTrue($user->can(Permission::UPDATE_USERS));
    }

    /**
     * @return void
     */
    public function testUserCanUpdateUsersInOwnPublicationAsAPublicationAdministrator()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::PUBLICATION_ADMINISTRATOR);
        $this->assertTrue($user->can(Permission::UPDATE_USERS_IN_OWN_PUBLICATION));
    }

    /**
     * @return void
     */
    public function testUserCannotUpdateUsersAsASubmitter()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::SUBMITTER);
        $this->assertFalse($user->can(Permission::UPDATE_USERS));
    }
}
