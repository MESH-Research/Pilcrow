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
    public function testCreationOfRoles()
    {
        $data = Role::factory()->make();
        $this->assertNotNull($data);
    }

    /**
     * @return void
     */
    public function testCreationOfUsers()
    {
        $data = User::factory()->make();
        $this->assertNotNull($data);
    }
}
