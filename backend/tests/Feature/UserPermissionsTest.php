<?php

namespace Tests\Feature;

use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class UserPermissionsTest extends TestCase
{
    use MakesGraphQLRequests, RefreshDatabase;

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
        foreach($roles as $role) {
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
        $permission = Permission::factory()->create(['name' => $this->test_permission]);
        $test_role = Role::factory()->create(['name' => $this->test_user_role]);
        $test_role->givePermissionTo($this->test_permission);
        $user->assignRole(Role::PUBLICATION_ADMINISTRATOR);
        $this->assertFalse($user->can($this->test_permission));
    }

    /**
     * @return void
     */
    public function testRoleForUserIsQueryableFromGraphqlEndpoint()
    {
        $test_role = Role::factory()->create(['name' => $this->test_user_role]);
        $user = User::factory()->create();
        $user->assignRole($this->test_user_role);
        $response = $this->graphQL(
            'query getUser($id: ID) {
                user(id: $id) {
                    id
                    name
                    roles {
                        id
                        name
                    }
                }
            }', ['id' => $user->id]
        );
        $expected_array = [
            0 => [
                'id' => (string) $test_role->id,
                'name' => 'Test User Role'
            ]
        ];
        $response->assertJsonPath("data.user.roles", $expected_array);
    }

    /**
     * @return void
     */
    public function testUserWithNoRoleReturnsAnEmptyArrayWhenQueriedFromGraphqlEndpoint()
    {
        $user = User::factory()->create();
        $response = $this->graphQL(
            'query getUser($id: ID) {
                user(id: $id) {
                    id
                    name
                    roles {
                        id
                        name
                    }
                }
            }', ['id' => $user->id]
        );
        $response->assertJsonPath("data.user.roles", []);
    }

    /**
     * @return void
     */
    public function testPermissionToUpdateUserForOthersExists()
    {
        /** @var $permission Permission */
        $permission = Permission::findByname(Permission::UPDATE_USER_FOR_OTHERS);
        $this->assertNotNull($permission);
        $this->assertEquals($permission->name, Permission::UPDATE_USER_FOR_OTHERS);
        $this->assertEquals(1, $permission->count());
    }

    /**
     * @return void
     */
    public function testUserCanUpdateUserForOthersAsAnApplicationAdministrator()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->assertTrue($user->can(Permission::UPDATE_USER_FOR_OTHERS));
    }

    /**
     * @return void
     */
    public function testUserCannotUpdateUserForOthersAsASubmitter()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::SUBMITTER);
        $this->assertFalse($user->can(Permission::UPDATE_USER_FOR_OTHERS));
    }

    /**
     * @return void
     */
    public function testPermissionToUpdateUserForOthersIsQueryableFromGraphqlEndpointForApplicationAdministrator()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $response = $this->graphQL(
            'query getUser($id: ID) {
                user(id: $id) {
                    id
                    name
                    roles {
                        id
                        name
                        permissions {
                            id
                            name
                        }
                    }
                }
            }', ['id' => $user->id]
        );
        $expected_array = [
            'id' => (string) $user->id,
            'name' => $user->name,
            'roles' => [
                0 => [
                    'id' => (string) 1,
                    'name' => Role::APPLICATION_ADMINISTRATOR,
                    'permissions' => [
                        0 => [
                            'id' => (string) 1,
                            'name' => Permission::UPDATE_USER_FOR_OTHERS
                        ]
                    ]
                ]
            ]
        ];
        $response->assertJsonPath("data.user", $expected_array);
    }

    /**
     * @return void
     */
    public function testPermissionToUpdateUserForOthersIsNotQueryableFromGraphqlEndpointForReviewer()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::REVIEWER);
        $response = $this->graphQL(
            'query getUser($id: ID) {
                user(id: $id) {
                    id
                    name
                    roles {
                        id
                        name
                        permissions {
                            id
                            name
                        }
                    }
                }
            }', ['id' => $user->id]
        );
        $expected_array = [
            'id' => (string) $user->id,
            'name' => $user->name,
            'roles' => [
                0 => [
                    'id' => (string) 5,
                    'name' => Role::REVIEWER,
                    'permissions' => [ ]
                ]
            ]
        ];
        $response->assertJsonPath("data.user", $expected_array);
    }
}
