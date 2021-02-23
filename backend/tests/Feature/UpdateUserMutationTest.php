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
            }',
            ['id' => $user->id]
        );
        $response->assertJsonPath('data.updateUser.username', 'testbrandnewusername');
    }

    public function testUserUpdateOwnDetailsToBeTheSame()
    {
        $user = User::factory()->create([
            'name' => 'testname',
            'email' => 'brandnew@gmail.com',
            'username' => 'testusername',
        ]);

        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation updateUser ($id: ID!){
                updateUser(id: $id,
                    user: {
                        name: "testname"
                        email: "brandnew@gmail.com"
                        username: "testusername"
                    }
                ) {
                    name
                    email
                    username
                }
            }',
            ['id' => $user->id]
        );
        $response->assertJsonPath('data.updateUser.username', 'testbrandnewusername');
    }

    /**
     * @return void
     */
    public function testUserCannotUpdateOthers(): void
    {
        $loggedInUser = User::factory()->create([
            'email' => 'loggedin@gmail.com',
            'username' => 'loggedinuser',
        ]);

        $userToUpdate = User::factory()->create([
            'email' => 'usertoupdate@gmail.com',
            'username' => 'usertoupdate',
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
            }',
            ['id' => $userToUpdate->id]
        );

        $response->assertJsonPath('data', null);
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
            'username' => 'usertoupdate',
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
            }',
            ['id' => $userToUpdate->id]
        );
        $response->assertJsonPath('data.updateUser.username', 'testbrandnewusername');
    }
}
