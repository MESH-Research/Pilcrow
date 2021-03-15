<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class UpdateUserMutationTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testUserCanUpdateOwnData(): void
    {
        $user = User::factory()->create([
            'name' => 'test',
            'email' => 'brandnew@gmail.com',
            'username' => 'testusername',
            'profile_metadata' => [
                'salutation' => '1',
            ],
        ]);

        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation updateUser ($id: ID!){
                updateUser(
                    user: {
                        id: $id,
                        username: "testbrandnewusername",
                        profile_metadata: {
                            salutation: "2"
                        }
                    }
                ) {
                    username
                    profile_metadata {
                        salutation
                    }
                }
            }',
            [
                'id' => $user->id,
            ]
        );

        $response->assertJsonPath('data.updateUser.username', 'testbrandnewusername');
        $response->assertJsonPath('data.updateUser.profile_metadata.salutation', '2');
    }

    public function testUserCanUpdateOwnDataToBeTheSame()
    {
        $user = User::factory()->create([
            'name' => 'testname',
            'email' => 'brandnew@gmail.com',
            'username' => 'testusername',
            'profile_metadata' => [
                'salutation' => '1',
            ],
        ]);

        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation updateUser ($id: ID!){
                updateUser(
                    user: {
                        id: $id,
                        name: "testname",
                        email: "brandnew@gmail.com",
                        username: "testusername",
                        profile_metadata: {
                            salutation: "1"
                        }
                    }
                ) {
                    name
                    email
                    username
                    profile_metadata {
                        salutation
                    }
                }
            }',
            [
                'id' => $user->id,
            ]
        );
        $response->assertJsonPath('data.updateUser.name', 'testname');
        $response->assertJsonPath('data.updateUser.email', 'brandnew@gmail.com');
        $response->assertJsonPath('data.updateUser.username', 'testusername');
        $response->assertJsonPath('data.updateUser.profile_metadata.salutation', '1');
    }

    /**
     * @return void
     */
    public function testUserCannotUpdateOthersData(): void
    {
        $loggedInUser = User::factory()->create([
            'email' => 'loggedin@gmail.com',
            'username' => 'loggedinuser',
        ]);

        $userToUpdate = User::factory()->create([
            'email' => 'usertoupdate@gmail.com',
            'username' => 'usertoupdate',
            'profile_metadata' => [
                'salutation' => '1',
            ],
        ]);

        $this->actingAs($loggedInUser);

        $response = $this->graphQL(
            'mutation updateUser ($id: ID!){
                updateUser(
                    user: {
                        id: $id,
                        username: "testbrandnewusername",
                        profile_metadata {
                            salutation: "2"
                        }
                    }
                ) {
                    username
                    profile_metadata {
                        salutation
                    }
                }
            }',
            [
                'id' => $userToUpdate->id,
            ]
        );

        $response->assertJsonPath('data', null);
    }

    /**
     * @return void
     */
    public function testApplicationAdministratorCanUpdateOthersData(): void
    {
        $loggedInUser = User::factory()->create();
        $loggedInUser->assignRole(Role::APPLICATION_ADMINISTRATOR);

        $userToUpdate = User::factory()->create([
            'email' => 'usertoupdate@gmail.com',
            'username' => 'usertoupdate',
            'profile_metadata' => [
                'salutation' => '1',
            ],
        ]);
        $this->actingAs($loggedInUser);
        $response = $this->graphQL(
            'mutation updateUser ($id: ID!){
                updateUser(
                    user: {
                        id: $id,
                        username: "testbrandnewusername",
                        profile_metadata: {
                            salutation: "2"
                        }
                    }
                ) {
                    username
                    profile_metadata {
                        salutation
                    }
                }
            }',
            [
                'id' => $userToUpdate->id,
            ]
        );
        $response->assertJsonPath('data.updateUser.username', 'testbrandnewusername');
        $response->assertJsonPath('data.updateUser.profile_metadata.salutation', '2');
    }

    public function testApplicationAdministratorCanUpdateOthersDataToBeTheSame(): void
    {
        $loggedInUser = User::factory()->create();
        $loggedInUser->assignRole(Role::APPLICATION_ADMINISTRATOR);

        $userToUpdate = User::factory()->create([
            'name' => 'testname',
            'email' => 'testemail@gmail.com',
            'username' => 'testusername',
            'profile_metadata' => [
                'salutation' => '1',
            ],
        ]);
        $this->actingAs($loggedInUser);
        $response = $this->graphQL(
            'mutation updateUser ($id: ID!){
                updateUser(
                    user: {
                        id: $id,
                        name: "testname",
                        email: "testemail@gmail.com",
                        username: "testusername",
                        profile_metadata: {
                            salutation: "2"
                        }
                    }
                ) {
                    name
                    email
                    username
                    profile_metadata {
                        salutation
                    }
                }
            }',
            [
                'id' => $userToUpdate->id,
            ]
        );
        $response->assertJsonPath('data.updateUser.name', 'testname');
        $response->assertJsonPath('data.updateUser.email', 'testemail@gmail.com');
        $response->assertJsonPath('data.updateUser.username', 'testusername');
        $response->assertJsonPath('data.updateUser.profile_metadata.salutation', '2');
    }
}
