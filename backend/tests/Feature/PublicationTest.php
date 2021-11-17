<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\PublicationUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class PublicationTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    public function testPublicationsCanBeCreatedWithCustomNamesThatAreNotDuplicates()
    {
        $publication = Publication::factory()->create(['name' => 'Custom Name']);
        $this->assertEquals($publication->name, 'Custom Name');

        $this->expectException(QueryException::class);
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
            ],
            [
                '        Test Publication with Whitespace       ',
                [
                    'createPublication' => [
                        'name' => 'Test Publication with Whitespace',
                    ],
                ],
            ],
            [
                '',
                null,
            ],
        ];
    }

    /**
     * @dataProvider publicationMutationProvider
     * @return void
     */
    public function testPublicationsCanBeCreatedViaMutationByAnApplicationAdministrator(mixed $publication_name, mixed $expected_data): void
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
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testPublicationsCannotBeCreatedViaMutationByARegularUser()
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
    public function testIndividualPublicationsCanBeQueriedById()
    {
        $publication = Publication::factory()->create([
            'name' => 'Test Publication for Querying an Individual Publication',
        ]);
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
                'name' => 'Test Publication for Querying an Individual Publication',
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testPublicationThatDoesNotExistCanBeQueriedById()
    {
        $response = $this->graphQL(
            'query GetPublication {
                publication (id: "Invalid ID") {
                    id
                    name
                }
            }'
        );
        $expected_data = [
            'publication' => null,
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testAllPublicationsCanBeQueriedByAnApplicationAdministrator()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->actingAs($user);

        $publication_1 = Publication::factory()->create([
            'name' => 'Test Publication 1 for Querying All Publications',
        ]);
        $publication_2 = Publication::factory()->create([
            'name' => 'Test Publication 2 for Querying All Publications',
        ]);
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
        $expected_data = [
            'publications' => [
                'data' => [
                    [
                        'id' => (string)$publication_1->id,
                        'name' => 'Test Publication 1 for Querying All Publications',
                    ],
                    [
                        'id' => (string)$publication_2->id,
                        'name' => 'Test Publication 2 for Querying All Publications',
                    ],
                ],
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testAllPublicationsCannotBeQueriedByARegularUser()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

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
        $expected_data = [
            'publications' => null,
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testPublicationsHaveAManyToManyRelationshipWithUsers()
    {
        $publication_count = 4;
        $user_count = 6;
        $users = User::factory()->count($user_count)->create();

        // Create publications and attach them to users randomly with random roles
        for ($i = 0; $i < $publication_count; $i++) {
            $random_user = $users->random();
            $random_role_id = Role::whereIn(
                'name',
                [
                    Role::PUBLICATION_ADMINISTRATOR,
                    Role::EDITOR,
                ]
            )
                ->get()
                ->pluck('id')
                ->random();
            $publication = Publication::factory()->hasAttached(
                $random_user,
                [
                    'role_id' => $random_role_id,
                ]
            )
                ->create();
            // Ensure at least one publication admin is attached to the publication.
            if ($random_role_id !== Role::PUBLICATION_ADMINISTRATOR_ROLE_ID) {
                $random_non_duplicate_user = $users->reject(function ($user) use ($random_user) {
                    return $user->id === $random_user->id;
                })->random();
                $publication->users()->attach(
                    $random_non_duplicate_user,
                    [
                        'role_id' => Role::PUBLICATION_ADMINISTRATOR_ROLE_ID,
                    ]
                );
            }
        }
        Publication::all()->map(function ($publication) {
            $this->assertGreaterThan(0, $publication->users->count());
            $this->assertLessThanOrEqual(2, $publication->users->count());
            $publication->users->map(function ($user) {
                $this->assertIsInt(
                    User::where(
                        'id',
                        $user->id
                    )->firstOrFail()->id
                );
            });
        });
        User::all()->map(function ($user) use ($publication_count) {
            $this->assertLessThanOrEqual($publication_count, $user->publications->count());
            $user->publications->map(function ($publication) {
                $this->assertIsInt(
                    Publication::where(
                        'id',
                        $publication->id
                    )->firstOrFail()->id
                );
            });
        });
    }

    /**
     * @return void
     */
    public function testUserRoleAndUserAreUniqueForAPublication()
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
        return [
            [
                [
                    'publication_user_role_id' => Role::SUBMITTER_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => Role::REVIEWER_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => Role::REVIEW_COORDINATOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => Role::EDITOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => 0,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => '',
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => null,
                    'allowed' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider createPublicationUserViaMutationAsAnEditorProvider
     * @return void
     */
    public function testCreatePublicationUserViaMutationAsAnEditor(array $case)
    {
        /** @var User $editor */
        $editor = User::factory()->create();
        $editor->assignRole(Role::EDITOR);
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
                'role_id' => $case['publication_user_role_id'],
                'publication_id' => $publication->id,
                'user_id' => $user_to_be_assigned->id,
            ]
        );
        $expected_mutation_response = null;
        if ($case['allowed']) {
            $expected_mutation_response = [
                'createPublicationUser' => [
                    'role_id' => $case['publication_user_role_id'],
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
        if ($case['allowed']) {
            array_push(
                $expected_query_response['publication']['users'],
                [
                    'id' => (string)$user_to_be_assigned->id,
                    'pivot' => [
                        'role_id' => $case['publication_user_role_id'],
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
        return [
            [
                [
                    'publication_user_role_id' => Role::SUBMITTER_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => Role::REVIEWER_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => Role::REVIEW_COORDINATOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => Role::EDITOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => 0,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => '',
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => null,
                    'allowed' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider deletePublicationUserViaMutationAsAnEditorProvider
     * @return void
     */
    public function testDeletePublicationUserViaMutationAsAnEditor(array $case)
    {
        /** @var User $editor */
        $editor = User::factory()->create();
        $editor->assignRole(Role::EDITOR);
        $this->actingAs($editor);
        $user_to_be_deleted = User::factory()->create();
        $publication_user_role_id_is_invalid = intval($case['publication_user_role_id']) <= 0;
        $publication = Publication::factory()
            ->hasAttached(
                $user_to_be_deleted,
                [
                    'role_id' => $publication_user_role_id_is_invalid ? Role::EDITOR_ROLE_ID : $case['publication_user_role_id'],
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
                'role_id' => $case['publication_user_role_id'],
                'publication_id' => $publication->id,
                'user_id' => $user_to_be_deleted->id,
            ]
        );
        $expected_mutation_response = null;
        if ($case['allowed']) {
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
        return [
            [
                [
                    'publication_user_role_id' => Role::SUBMITTER_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => Role::REVIEWER_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => Role::REVIEW_COORDINATOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => Role::EDITOR_ROLE_ID,
                    'allowed' => true,
                ],
            ],
            [
                [
                    'publication_user_role_id' => 0,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => '',
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => null,
                    'allowed' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider createPublicationUserViaMutationAsAnApplicationAdministratorProvider
     * @return void
     */
    public function testCreatePublicationUserViaMutationAsAnApplicationAdministrator(array $case)
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
                'role_id' => $case['publication_user_role_id'],
                'publication_id' => $publication->id,
                'user_id' => $user_to_be_assigned->id,
            ]
        );
        $expected_mutation_response = null;
        if ($case['allowed']) {
            $expected_mutation_response = [
                'createPublicationUser' => [
                    'role_id' => $case['publication_user_role_id'],
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
        if ($case['allowed']) {
            array_push(
                $expected_query_response['publication']['users'],
                [
                    'id' => (string)$user_to_be_assigned->id,
                    'pivot' => [
                        'role_id' => $case['publication_user_role_id'],
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
        return [
            [
                [
                    'publication_user_role_id' => Role::SUBMITTER_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => Role::REVIEWER_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => Role::REVIEW_COORDINATOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => Role::EDITOR_ROLE_ID,
                    'allowed' => true,
                ],
            ],
            [
                [
                    'publication_user_role_id' => 0,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => '',
                    'allowed' => false,
                ],
            ],
            [
                [
                    'publication_user_role_id' => null,
                    'allowed' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider deletePublicationUserViaMutationAsAnApplicationAdministratorProvider
     * @return void
     */
    public function testDeletePublicationUserViaMutationAsAnApplicationAdministrator(array $case)
    {
        /** @var User $editor */
        $editor = User::factory()->create();
        $editor->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->actingAs($editor);
        $user_to_be_deleted = User::factory()->create();
        $publication_user_role_id_is_invalid = intval($case['publication_user_role_id']) <= 0;
        $publication = Publication::factory()
            ->hasAttached(
                $user_to_be_deleted,
                [
                    'role_id' => $publication_user_role_id_is_invalid ? Role::EDITOR_ROLE_ID : $case['publication_user_role_id'],
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
                'role_id' => $case['publication_user_role_id'],
                'publication_id' => $publication->id,
                'user_id' => $user_to_be_deleted->id,
            ]
        );
        $expected_mutation_response = null;
        if ($case['allowed']) {
            $expected_mutation_response = [
                'deletePublicationUser' => [
                    'id' => (string)$publication_user->id,
                ],
            ];
        }
        $response->assertJsonPath('data', $expected_mutation_response);
    }
}
