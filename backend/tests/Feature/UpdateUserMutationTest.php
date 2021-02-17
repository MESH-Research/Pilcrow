<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateUserTest extends TestCase
{
    use MakesGraphQLRequests, RefreshDatabase;
    /**
     * @return void
     */
    public function testUserUpdateOwn(): void
    {
        $user = User::factory()->create([
            'email' => 'brandnew@gmail.com',
            'username' => 'testusername',
        ]);

        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation updateUser ($id: ID!){
                updateUser(id: $id,
                    user: {
                        username: "testbrandnewusername"
                    }
                ) {
                    username
                }
            }', ['id' => $user->id]
        );
        $response->assertJsonPath("data.updateUser.username", "testbrandnewusername");
    }

    /**
     * @return void
     */
    public function testUserCannotUpdateOthers(): void {
        $loggedInUser = User::factory()->create([
            'email' => 'loggedin@gmail.com',
            'username' => 'loggedinuser',
        ]);

        $userToUpdate = User::factory()->create([
            'email' => 'usertoupdate@gmail.com',
            'username' => 'usertoupdate'
        ]);

        $this->actingAs($loggedInUser);

        $response = $this->graphQL(
            'mutation updateUser ($id: ID!){
                updateUser(id: $id,
                    user: {
                        username: "testbrandnewusername"
                    }
                ) {
                    username
                }
            }', ['id' => $userToUpdate->id]
        );

        $response->assertJsonPath("data", null);
    }

    /**
     * @return void
     */
    public function testApplicationAdministratorCanUpdateOthers(): void
    {
        $loggedInUser = User::factory()->create();
        $loggedInUser->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $userToUpdate = User::factory()->create([
            'email' => 'usertoupdate@gmail.com',
            'username' => 'usertoupdate'
        ]);
        $this->actingAs($loggedInUser);
        $response = $this->graphQL(
            'mutation updateUser ($id: ID!){
                updateUser(id: $id,
                    user: {
                        username: "testbrandnewusername"
                    }
                ) {
                    username
                }
            }', ['id' => $userToUpdate->id]
        );
        $response->assertJsonPath("data.updateUser.username", "testbrandnewusername");
    }
}
