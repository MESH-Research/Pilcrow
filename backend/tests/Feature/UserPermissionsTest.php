<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class UserPermissionsTest extends TestCase
{
    use RefreshDatabase;

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
    public function testCreationOfApplicationAdministratorRole()
    {
        $name = 'Application Administrator';
        $role = Role::factory()->create([
            'name' => $name
        ]);
        $this->assertEquals($role->name, $name);
    }

    /**
     * @return void
     */
    public function testAssignmentOfApplicationAdministratorRole()
    {
        $name = 'Application Administrator';
        $user = User::factory()->create();
        $role = Role::factory()->create([
            'name' => $name
        ]);
        $user->assignRole($name);
        $this->assertTrue($user->hasRole($name));
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
    public function testCreationOfPermissionToCreatePublications()
    {
        $name = 'create publications';
        $permission = Permission::factory()->create([
            'name' => $name
        ]);
        $this->assertEquals($permission->name, $name);
    }

    /**
     * @return void
     */
    public function testAssignmentOfPermissionToCreatePublicationsToRole()
    {
        $name = 'create publications';
        $permission = Permission::factory()->create([
            'name' => $name
        ]);
        $role = Role::factory()->create([
            'name' => 'Application Administrator'
        ]);
        $role->givePermissionTo($name);
        $this->assertTrue($role->hasPermissionTo($name));
    }

    /**
     * @return void
     */
    public function testUserHasPermissionToCreatePublicationsByAssignedRole()
    {
        $name = 'create publications';
        $user = User::factory()->create();
        $permission = Permission::factory()->create([
            'name' => $name
        ]);
        $role = Role::factory()->create([
            'name' => 'Application Administrator'
        ]);
        $role->givePermissionTo($name);
        $user->assignRole('Application Administrator');
        $this->assertTrue($user->can($name));
    }

    /**
     * @return void
     */
    public function testUserDoesNotHavePermissionToCreatePublicationsByAssignedRole()
    {
        $name = 'create publications';
        $user = User::factory()->create();
        $permission = Permission::factory()->create([
            'name' => $name
        ]);
        $role_1 = Role::factory()->create([
            'name' => 'Application Administrator'
        ]);
        $role_2 = Role::factory()->create([
            'name' => 'Publication Administrator'
        ]);
        $role_1->givePermissionTo($name);
        $user->assignRole('Publication Administrator');
        $this->assertFalse($user->can($name));
    }

    /**
     * @return void
     */
    public function testThatUserRoleRecordsExist()
    {
        $roles = [
            Role::APPLICATION_ADMINISTRATOR,
            Role::PUBLICATION_ADMINISTRATOR,
            Role::EDITOR,
            Role::REVIEW_COORDINATOR,
            Role::REVIEWER,
            Role::SUBMITTER,
        ];
        foreach($roles as $role) {
            $record = Role::where('name', $role);
            $this->assertTrue($record->count() > 0);
        }
    }
}
