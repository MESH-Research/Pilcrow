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
}
