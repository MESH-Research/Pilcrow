<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\ApiTestCase;

class UserPermissionsTest extends ApiTestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    private $test_permission = 'test permission';
    private $test_user_role = 'Test User Role';

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
            }',
            ['id' => $user->id]
        );
        $expected_array = [
            0 => [
                'id' => (string)$test_role->id,
                'name' => 'Test User Role',
            ],
        ];
        $response->assertJsonPath('data.user.roles', $expected_array);
    }

    /**
     * @return void
     */
    public function testUserWithNoRoleReturnsAnEmptyArrayWhenRolesForUserAreQueriedFromGraphqlEndpoint()
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
            }',
            ['id' => $user->id]
        );
        $response->assertJsonPath('data.user.roles', []);
    }

    /**
     * @return array
     */
    public function permissionsByRoleProvider()
    {
        return [
            [
                Role::APPLICATION_ADMINISTRATOR,
                [
                    'roles' => [
                        0 => [
                            'id' => Role::APPLICATION_ADMINISTRATOR_ROLE_ID,
                            'name' => Role::APPLICATION_ADMINISTRATOR,
                            'permissions' => [
                                0 => [
                                    'id' => '1',
                                    'name' => Permission::UPDATE_USERS,
                                ],
                                1 => [
                                    'id' => '3',
                                    'name' => Permission::CREATE_PUBLICATION,
                                ],
                                2 => [
                                    'id' => '4',
                                    'name' => Permission::VIEW_ALL_PUBLICATIONS,
                                ],
                                3 => [
                                    'id' => '5',
                                    'name' => Permission::ASSIGN_REVIEWER,
                                ],
                                4 => [
                                    'id' => '6',
                                    'name' => Permission::UNASSIGN_REVIEWER,
                                ],
                                5 => [
                                    'id' => '7',
                                    'name' => Permission::ASSIGN_REVIEW_COORDINATOR,
                                ],
                                6 => [
                                    'id' => '8',
                                    'name' => Permission::UNASSIGN_REVIEW_COORDINATOR,
                                ],
                                7 => [
                                    'id' => '9',
                                    'name' => Permission::ASSIGN_EDITOR,
                                ],
                                8 => [
                                    'id' => '10',
                                    'name' => Permission::UNASSIGN_EDITOR,
                                ],
                                9 => [
                                    'id' => '11',
                                    'name' => Permission::UPDATE_SITE_SETTINGS,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                Role::PUBLICATION_ADMINISTRATOR,
                [
                    'roles' => [
                        0 => [
                            'id' => Role::PUBLICATION_ADMINISTRATOR_ROLE_ID,
                            'name' => Role::PUBLICATION_ADMINISTRATOR,
                            'permissions' => [
                                0 => [
                                    'id' => '2',
                                    'name' => Permission::UPDATE_USERS_IN_OWN_PUBLICATION,
                                ],
                                1 => [
                                    'id' => '5',
                                    'name' => Permission::ASSIGN_REVIEWER,
                                ],
                                2 => [
                                    'id' => '6',
                                    'name' => Permission::UNASSIGN_REVIEWER,
                                ],
                                3 => [
                                    'id' => '7',
                                    'name' => Permission::ASSIGN_REVIEW_COORDINATOR,
                                ],
                                4 => [
                                    'id' => '8',
                                    'name' => Permission::UNASSIGN_REVIEW_COORDINATOR,
                                ],
                                5 => [
                                    'id' => '9',
                                    'name' => Permission::ASSIGN_EDITOR,
                                ],
                                6 => [
                                    'id' => '10',
                                    'name' => Permission::UNASSIGN_EDITOR,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                Role::EDITOR,
                [
                    'roles' => [
                        0 => [
                            'id' => Role::EDITOR_ROLE_ID,
                            'name' => Role::EDITOR,
                            'permissions' => [
                                0 => [
                                    'id' => '5',
                                    'name' => Permission::ASSIGN_REVIEWER,
                                ],
                                1 => [
                                    'id' => '6',
                                    'name' => Permission::UNASSIGN_REVIEWER,
                                ],
                                2 => [
                                    'id' => '7',
                                    'name' => Permission::ASSIGN_REVIEW_COORDINATOR,
                                ],
                                3 => [
                                    'id' => '8',
                                    'name' => Permission::UNASSIGN_REVIEW_COORDINATOR,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // Authorization related to submissions for review coordinators resides in SubmissionUserPolicy.php
            [
                Role::REVIEW_COORDINATOR,
                [
                    'roles' => [
                        0 => [
                            'id' => Role::REVIEW_COORDINATOR_ROLE_ID,
                            'name' => Role::REVIEW_COORDINATOR,
                            'permissions' => [ ],
                        ],
                    ],
                ],
            ],
            [
                Role::REVIEWER,
                [
                    'roles' => [
                        0 => [
                            'id' => Role::REVIEWER_ROLE_ID,
                            'name' => Role::REVIEWER,
                            'permissions' => [ ],
                        ],
                    ],
                ],
            ],
            [
                Role::SUBMITTER,
                [
                    'roles' => [
                        0 => [
                            'id' => Role::SUBMITTER_ROLE_ID,
                            'name' => Role::SUBMITTER,
                            'permissions' => [ ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider permissionsByRoleProvider
     * @return void
     */
    public function testPermissionsByRoleAreQueryableFromGraphqlEndpoint($role, $expected_array)
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        $response = $this->graphQL(
            'query getUser($id: ID) {
                user(id: $id) {
                    roles {
                        id
                        name
                        permissions {
                            id
                            name
                        }
                    }
                }
            }',
            ['id' => $user->id]
        );
        $response->assertJsonPath('data.user', $expected_array);
    }
}
