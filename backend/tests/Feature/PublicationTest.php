<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\PublicationUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Exceptions\ValidationException;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class PublicationTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    public function testNoDuplicateNames()
    {
        $publication = Publication::factory()->create(['name' => 'Custom Name']);
        $this->assertEquals($publication->name, 'Custom Name');

        $this->expectException(ValidationException::class);
        Publication::factory()->create(['name' => 'Custom Name']);
    }

    /**
     * @return array
     */
    public function publicationMutationProvider(): array
    {
        return [
            [
                'Test Publication',
                [
                    'createPublication' => [
                        'name' => 'Test Publication',
                    ],
                ],
                'Unable to create publication',
            ],
            [
                '        Test Publication with Whitespace       ',
                [
                    'createPublication' => [
                        'name' => 'Test Publication with Whitespace',
                    ],
                ],
                'Whitespace should be trimmed around name',
            ],
            [
                '',
                null,
                'Name must be required',
            ],
        ];
    }

    /**
     * @dataProvider publicationMutationProvider
     * @return void
     */
    public function testCreation(mixed $publication_name, mixed $expected_data, string $message): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->actingAs($user);
        $response = $this->graphQL(
            'mutation CreatePublication ($publication_name: String) {
                createPublication(publication:{name: $publication_name}) {
                    name
                }
            }',
            [ 'publication_name' => $publication_name ]
        );
        $json = $response->json();
        $this->assertSame($json['data'] ?? null, $expected_data, $message);
    }

    /**
     * @return void
     */
    public function testCreationRequiresAppAdmin()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->graphQL(
            'mutation CreatePublication {
                createPublication(publication:{name:"Test Publication Created by Regular User"}) {
                    id
                    name
                }
            }'
        );
        $response->assertJsonPath('data', null);
    }

    /**
     * @return void
     */
    public function testCanBeQueriedById()
    {
        $publication = Publication::factory()->create();
        $response = $this->graphQL(
            'query GetPublication($id: ID!) {
                publication (id: $id) {
                    id
                    name
                }
            }',
            [ 'id' => $publication->id ]
        );
        $expected_data = [
            'publication' => [
                'id' => (string)$publication->id,
                'name' => $publication->name,
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testAppAdminQueryShowsHiddenItems()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->actingAs($user);

        Publication::factory()->count(2)->create();
        Publication::factory()->hidden()->count(2)->create();

        $response = $this->graphQL(
            'query GetPublications {
                publications {
                    data {
                        id
                        name
                    }
                }
            }'
        );

        $json = $response->json('data.publications.data');
        $this->assertCount(4, $json);
    }

    /**
     * @return void
     */
    public function testQueryFiltersHiddenItems()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        Publication::factory()->count(2)->create();
        Publication::factory()->hidden()->count(2)->create();
        $response = $this->graphQL(
            'query GetPublications {
                publications(is_publicly_visible: true) {
                    data {
                        id
                        name
                    }
                }
            }'
        );
        $json = $response->json('data.publications.data');

        $this->assertCount(2, $json);
    }

    /**
     * @return void
     */
    public function testUserRoleAndUserMustBeUniqueForAPublication()
    {
        $user = User::factory()->create();
        $role_id = Role::where('name', Role::PUBLICATION_ADMINISTRATOR)->first()->id;

        $publication = Publication::factory()->hasAttached(
            $user,
            [
                'role_id' => $role_id,
            ]
        )
            ->create();
        $this->expectException(QueryException::class);
        $publication->users()->attach(
            $user,
            [
                'role_id' => $role_id,
            ]
        );
        $publication_pivot_data = PublicationUser::where(
            [
                'user_id' => $user->id,
                'role_id' => $role_id,
                'publication_id' => $publication->id,
            ]
        )
            ->get();
        $this->assertEquals(1, $publication_pivot_data->count());
    }

    /**
     * @return array
     */
    public function createPublicationUserViaMutationAsAnEditorProvider(): array
    {
        //@codingStandardsIgnoreStart
        return [
            //User Role ID,                      Allowed?
            [ Role::SUBMITTER_ROLE_ID,          false ],
            [ Role::REVIEWER_ROLE_ID,           false ],
            [ Role::REVIEW_COORDINATOR_ROLE_ID, false ],
            [ Role::EDITOR_ROLE_ID,             false ],
            [ 0,                                false ], //TODO: These should be tested in the context of validating role ids, not repeatedly as a possible input
            [ '',                               false ],
            [ null,                             false ],
        ];
        //@codingStandardsIgnoreEnd
    }

    /**
     * @dataProvider createPublicationUserViaMutationAsAnEditorProvider
     * @return void
     */
    public function testCreatePublicationUserViaMutationAsAnEditor($userRoleId, bool $allowed)
    {
        /** @var User $editor */
        $editor = User::factory()->create();
        $editor->assignRole(Role::EDITOR); //TODO: What does Role::EDITOR mean when not referencing a publication.
        $this->actingAs($editor);
        $user_to_be_assigned = User::factory()->create();
        $publication = Publication::factory()
            ->hasAttached(
                $editor,
                [
                    'role_id' => 3,
                ]
            )
            ->create([
                'name' => 'Test Publication for Publication User Assignment Via Mutation',
            ]);
        $mutation_response = $this->graphQL(
            'mutation CreatePublicationUser ($role_id: ID!, $publication_id: ID!, $user_id: ID!) {
                createPublicationUser(
                    publication_user: { role_id: $role_id, publication_id: $publication_id, user_id: $user_id }
                ) {
                    role_id
                    publication_id
                    user_id
                }
            }',
            [
                'role_id' => $userRoleId,
                'publication_id' => $publication->id,
                'user_id' => $user_to_be_assigned->id,
            ]
        );
        $expected_mutation_response = null;
        if ($allowed) {
            $expected_mutation_response = [
                'createPublicationUser' => [
                    'role_id' => $userRoleId,
                    'publication_id' => (string)$publication->id,
                    'user_id' => (string)$user_to_be_assigned->id,
                ],
            ];
        }
        $mutation_response->assertJsonPath('data', $expected_mutation_response);
        $query_response = $this->graphQL(
            'query GetPublication ($id: ID!) {
                publication( id: $id ) {
                    users {
                        id
                        pivot {
                            role_id
                        }
                    }
                }
            }',
            [
                'id' => $publication->id,
            ]
        );
        $expected_query_response = [
            'publication' => [
                'users' => [
                    [
                        'id' => (string)$editor->id,
                        'pivot' => [
                            'role_id' => Role::EDITOR_ROLE_ID,
                        ],
                    ],
                ],
            ],
        ];
        if ($allowed) {
            array_push(
                $expected_query_response['publication']['users'],
                [
                    'id' => (string)$user_to_be_assigned->id,
                    'pivot' => [
                        'role_id' => $userRoleId,
                    ],
                ],
            );
        }
        $query_response->assertJsonPath('data', $expected_query_response);
    }

    /**
     * @return array
     */
    public function deletePublicationUserViaMutationAsAnEditorProvider(): array
    {
        //@codingStandardsIgnoreStart
        return [
            //User Role ID                      Allowed?
            [ Role::SUBMITTER_ROLE_ID,          false ], //TODO: Everything is false, whats the point ?
            [ Role::REVIEWER_ROLE_ID,           false ],
            [ Role::REVIEW_COORDINATOR_ROLE_ID, false ],
            [ Role::EDITOR_ROLE_ID,             false ],
            [ 0,                                false ],
            [ '',                               false ],
            [ null,                             false ],
        ];
        //@codingStandardsIgnoreEnd
    }

    /**
     * @dataProvider deletePublicationUserViaMutationAsAnEditorProvider
     * @return void
     */
    public function testDeletePublicationUserViaMutationAsAnEditor($userRoleId, bool $allowed)
    {
        /** @var User $editor */
        $editor = User::factory()->create();
        $editor->assignRole(Role::EDITOR);
        $this->actingAs($editor);
        $user_to_be_deleted = User::factory()->create();
        $publication_user_role_id_is_invalid = intval($userRoleId) <= 0; //TODO: Overly verbose variable name
        $publication = Publication::factory()
            ->hasAttached(
                $user_to_be_deleted,
                [
                    'role_id' => $publication_user_role_id_is_invalid ? Role::EDITOR_ROLE_ID : $userRoleId, //TODO: What is the point of this ? If the role is invalid we just pretend it says EDITOR?
                ]
            )
            ->hasAttached(
                $editor,
                [
                    'role_id' => 3,
                ]
            )
            ->create([
                'name' => 'Test Publication for Publication User Unassignment Via Mutation',
            ]);

        $publication_user = PublicationUser::firstOrFail();
        $response = $this->graphQL(
            'mutation DeletePublicationUser ($role_id: ID!, $publication_id: ID!, $user_id: ID!) {
                deletePublicationUser(
                    publication_user: { role_id: $role_id, publication_id: $publication_id, user_id: $user_id }
                ) {
                    id
                }
            }',
            [
                'role_id' => $userRoleId,
                'publication_id' => $publication->id,
                'user_id' => $user_to_be_deleted->id,
            ]
        );
        $expected_mutation_response = null;
        if ($allowed) {
            $expected_mutation_response = [
                'deletePublicationUser' => [
                    'id' => (string)$publication_user->id,
                ],
            ];
        }
        $response->assertJsonPath('data', $expected_mutation_response);
    }

    /**
     * @return array
     */
    public function createPublicationUserViaMutationAsAnApplicationAdministratorProvider(): array
    {
        //@codingStandardsIgnoreStart
        return [
            //User Role                         Allowed
            [ Role::SUBMITTER_ROLE_ID,          false ],
            [ Role::REVIEWER_ROLE_ID,           false ],
            [ Role::REVIEW_COORDINATOR_ROLE_ID, false ],
            [ Role::EDITOR_ROLE_ID,             true  ],
            [ 0,                                false ],
            [ '',                               false ],
            [ null,                             false ],
        ];
        //@codingStandardsIgnoreEnd
    }

    /**
     * @dataProvider createPublicationUserViaMutationAsAnApplicationAdministratorProvider
     * @return void
     */
    public function testCreatePublicationUserViaMutationAsAnApplicationAdministrator($userRoleId, bool $allowed)
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $admin->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->actingAs($admin);
        $user_to_be_assigned = User::factory()->create();
        $publication = Publication::factory()
            ->create([
                'name' => 'Test Publication for Publication User Assignment Via Mutation',
            ]);
        $mutation_response = $this->graphQL(
            'mutation CreatePublicationUser ($role_id: ID!, $publication_id: ID!, $user_id: ID!) {
                createPublicationUser(
                    publication_user: { role_id: $role_id, publication_id: $publication_id, user_id: $user_id }
                ) {
                    role_id
                    publication_id
                    user_id
                }
            }',
            [
                'role_id' => $userRoleId,
                'publication_id' => $publication->id,
                'user_id' => $user_to_be_assigned->id,
            ]
        );
        $expected_mutation_response = null;
        if ($allowed) {
            $expected_mutation_response = [
                'createPublicationUser' => [
                    'role_id' => $userRoleId,
                    'publication_id' => (string)$publication->id,
                    'user_id' => (string)$user_to_be_assigned->id,
                ],
            ];
        }
        $mutation_response->assertJsonPath('data', $expected_mutation_response);
        $query_response = $this->graphQL(
            'query GetPublication ($id: ID!) {
                publication( id: $id ) {
                    users {
                        id
                        pivot {
                            role_id
                        }
                    }
                }
            }',
            [
                'id' => $publication->id,
            ]
        );
        $expected_query_response = [
            'publication' => [
                'users' => [ ],
            ],
        ];
        if ($allowed) {
            array_push(
                $expected_query_response['publication']['users'],
                [
                    'id' => (string)$user_to_be_assigned->id,
                    'pivot' => [
                        'role_id' => $userRoleId,
                    ],
                ],
            );
        }
        $query_response->assertJsonPath('data', $expected_query_response);
    }

    /**
     * @return array
     */
    public function deletePublicationUserViaMutationAsAnApplicationAdministratorProvider(): array
    {
        //@codingStandardsIgnoreStart
        return [
            // User Role ID                     Allowed
            [ Role::SUBMITTER_ROLE_ID,          false ],
            [ Role::REVIEWER_ROLE_ID,           false ],
            [ Role::REVIEW_COORDINATOR_ROLE_ID, false ],
            [ Role::EDITOR_ROLE_ID,             true  ],
            [ 0,                                false ],
            [ '',                               false ],
            [ null,                             false ],
        ];
        //@codingStandardsIgnoreEnd
    }

    /**
     * @dataProvider deletePublicationUserViaMutationAsAnApplicationAdministratorProvider
     * @return void
     */
    public function testDeletePublicationUserViaMutationAsAnApplicationAdministrator($userRoleId, bool $allowed)
    {
        /** @var User $editor */
        $editor = User::factory()->create();
        $editor->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->actingAs($editor);
        $user_to_be_deleted = User::factory()->create();
        $publication_user_role_id_is_invalid = intval($userRoleId) <= 0;
        $publication = Publication::factory()
            ->hasAttached(
                $user_to_be_deleted,
                [
                    'role_id' => $publication_user_role_id_is_invalid ? Role::EDITOR_ROLE_ID : $userRoleId,
                ]
            )
            ->create([
                'name' => 'Test Publication for Publication User Unassignment Via Mutation',
            ]);

        $publication_user = PublicationUser::firstOrFail();
        $response = $this->graphQL(
            'mutation DeletePublicationUser ($role_id: ID!, $publication_id: ID!, $user_id: ID!) {
                deletePublicationUser(
                    publication_user: { role_id: $role_id, publication_id: $publication_id, user_id: $user_id }
                ) {
                    id
                }
            }',
            [
                'role_id' => $userRoleId,
                'publication_id' => $publication->id,
                'user_id' => $user_to_be_deleted->id,
            ]
        );
        $expected_mutation_response = null;
        if ($allowed) {
            $expected_mutation_response = [
                'deletePublicationUser' => [
                    'id' => (string)$publication_user->id,
                ],
            ];
        }
        $response->assertJsonPath('data', $expected_mutation_response);
    }

    public function canSavePublicationStyleCriteria()
    {
        $this->graphQL(
        )
    }
}
