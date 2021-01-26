<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;

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
}
