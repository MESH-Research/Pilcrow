<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use PHPUnit\Framework\Attributes\DataProvider;
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
        $this->beAppAdmin();
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
        $this->beAppAdmin();
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
    public static function permissionsByRoleProvider()
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
                                10 => [
                                    'id' => '12',
                                    'name' => Permission::MODERATE_AVATARS,
                                ],
                                11 => [
                                    'id' => '13',
                                    'name' => Permission::UPLOAD_AVATAR,
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
                                7 => [
                                    'id' => '13',
                                    'name' => Permission::UPLOAD_AVATAR,
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
                                4 => [
                                    'id' => '13',
                                    'name' => Permission::UPLOAD_AVATAR,
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
                            'permissions' => [
                                0 => [
                                    'id' => '13',
                                    'name' => Permission::UPLOAD_AVATAR,
                                ],
                            ],
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
                            'permissions' => [
                                0 => [
                                    'id' => '13',
                                    'name' => Permission::UPLOAD_AVATAR,
                                ],
                            ],
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
                            'permissions' => [
                                0 => [
                                    'id' => '13',
                                    'name' => Permission::UPLOAD_AVATAR,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return void
     */
    #[DataProvider('permissionsByRoleProvider')]
    public function testPermissionsByRoleAreQueryableFromGraphqlEndpoint($role, $expected_array)
    {
        $this->beAppAdmin();
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

    /**
     * Regression lock: User.permissions must include role-inherited
     * permissions, not just direct user->permission assignments.
     * Application Administrator inherits `moderate avatars` via the
     * role; earlier the field used @belongsToMany which returned the
     * direct-only set and silently hid role permissions from clients.
     *
     * @return void
     */
    public function testUserPermissionsIncludesRoleInheritedPermissions(): void
    {
        $admin = $this->beAppAdmin();

        $response = $this->graphQL(
            '{ currentUser { id permissions { name } } }'
        );

        $names = collect($response->json('data.currentUser.permissions'))
            ->pluck('name')
            ->all();

        // Role-inherited — if @belongsToMany ever comes back this fails.
        $this->assertContains(Permission::MODERATE_AVATARS, $names);
        $this->assertContains(Permission::UPDATE_USERS, $names);
        $this->assertContains(Permission::UPLOAD_AVATAR, $names);
        $this->assertEqualsCanonicalizing(
            $names,
            $admin->getAllPermissions()->pluck('name')->all(),
            'GraphQL should expose exactly getAllPermissions()'
        );
    }
}
