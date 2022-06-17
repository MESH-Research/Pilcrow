<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\PublicationUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
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
    public function publicationContentMutationProvider(): array
    {
        return [
            [
                [
                    'name' => 'Test Publication',
                    'home_page_content' => 'Amet animi quaerat eum sint placeat aut ratione iure. Quod dolor esse et. Error et tempora ipsa eum eos sequi facilis. A ipsam enim ullam minima. | Aut quam repellat ut nemo qui rerum quam. Veniam aut amet ullam nam eum odit laboriosam. Praesentium nulla similique omnis sed dolor. Et impedit quasi odit veritatis.',
                    'new_submission_content' => 'Voluptatem nam quidem perspiciatis. Qui sed quis harum aut porro maxime. Illo ipsa sint nobis repudiandae a voluptatem. Aut nostrum sunt soluta possimus.',
                ],
                [
                    'createPublication' => [
                        'name' => 'Test Publication',
                        'home_page_content' => 'Amet animi quaerat eum sint placeat aut ratione iure. Quod dolor esse et. Error et tempora ipsa eum eos sequi facilis. A ipsam enim ullam minima. | Aut quam repellat ut nemo qui rerum quam. Veniam aut amet ullam nam eum odit laboriosam. Praesentium nulla similique omnis sed dolor. Et impedit quasi odit veritatis.',
                        'new_submission_content' => 'Voluptatem nam quidem perspiciatis. Qui sed quis harum aut porro maxime. Illo ipsa sint nobis repudiandae a voluptatem. Aut nostrum sunt soluta possimus.',
                    ],
                ],
            ],
            [
                [
                    'name' => 'Test Publication',
                    'home_page_content' => 'Amet animi <div class="example">quaerat eum sint</div> <i>placeat</i> aut <u>ratione</u> <b>iure</b>. Quod <ul><li>dolor</li> <li>esse</li></ul> et. Error et <b>tempora</b> ipsa eum eos sequi facilis. A ipsam enim ullam minima. | Aut quam repellat ut nemo qui rerum quam. Veniam aut amet ullam nam eum odit laboriosam. Praesentium nulla similique omnis sed dolor. Et impedit quasi odit veritatis.',
                    'new_submission_content' => 'Voluptatem <p style="font-size:12px">nam quidem perspiciatis</p>. Qui sed quis harum aut porro maxime. Illo ipsa sint nobis repudiandae a voluptatem. Aut nostrum sunt soluta possimus.',
                ],
                [
                    'createPublication' => [
                        'name' => 'Test Publication',
                        'home_page_content' => 'Amet animi <div>quaerat eum sint</div> <i>placeat</i> aut <u>ratione</u> <b>iure</b>. Quod <ul><li>dolor</li> <li>esse</li></ul> et. Error et <b>tempora</b> ipsa eum eos sequi facilis. A ipsam enim ullam minima. | Aut quam repellat ut nemo qui rerum quam. Veniam aut amet ullam nam eum odit laboriosam. Praesentium nulla similique omnis sed dolor. Et impedit quasi odit veritatis.',
                        'new_submission_content' => 'Voluptatem <p>nam quidem perspiciatis</p>. Qui sed quis harum aut porro maxime. Illo ipsa sint nobis repudiandae a voluptatem. Aut nostrum sunt soluta possimus.',
                    ],
                ],
            ],
            [
                [
                    'name' => 'Test Publication',
                    'home_page_content' => 'Amet animi <a href="https://google.com/">quaerat</a> eum sint placeat aut ratione iure. Quod dolor esse et. Error et tempora ipsa eum eos sequi facilis. A ipsam <strong>enim</strong> ullam minima. | Aut quam <span>repellat</span> ut nemo qui rerum quam. <td>Veniam</td> aut amet ullam nam eum odit laboriosam. Praesentium nulla similique omnis sed dolor. Et impedit quasi odit veritatis.',
                    'new_submission_content' => 'Voluptatem <p style="font-size:12px">nam quidem perspiciatis</p>. Qui sed quis harum aut porro maxime. Illo ipsa sint <ol><li>dolor</li> <li>esse</li></ol> repudiandae a voluptatem. <br>Aut nostrum sunt soluta possimus.',
                ],
                [
                    'createPublication' => [
                        'name' => 'Test Publication',
                        'home_page_content' => 'Amet animi <a href="https://google.com/">quaerat</a> eum sint placeat aut ratione iure. Quod dolor esse et. Error et tempora ipsa eum eos sequi facilis. A ipsam enim ullam minima. | Aut quam repellat ut nemo qui rerum quam. Veniam aut amet ullam nam eum odit laboriosam. Praesentium nulla similique omnis sed dolor. Et impedit quasi odit veritatis.',
                      'new_submission_content' => 'Voluptatem <p>nam quidem perspiciatis</p>. Qui sed quis harum aut porro maxime. Illo ipsa sint <ol><li>dolor</li> <li>esse</li></ol> repudiandae a voluptatem. <br />Aut nostrum sunt soluta possimus.',
                    ],
                ],
            ],
            [
                [
                    'name' => 'Test Publication',
                    'home_page_content' => '',
                    'new_submission_content' => '',
                ],
                [
                    'createPublication' => [
                        'name' => 'Test Publication',
                        'home_page_content' => '',
                        'new_submission_content' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider publicationContentMutationProvider
     * @return void
     */
    public function testContentCreation(mixed $publication_data, mixed $expected_data): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->actingAs($user);
        $response = $this->graphQL(
            'mutation CreatePublication ($publication_name: String, $home_page_content: String, $new_submission_content: String) {
                createPublication(publication:{name: $publication_name home_page_content: $home_page_content new_submission_content: $new_submission_content}) {
                    name,
                    home_page_content,
                    new_submission_content,
                }
            }',
            [
              'publication_name' => $publication_data['name'],
              'home_page_content' => $publication_data['home_page_content'],
              'new_submission_content' => $publication_data['new_submission_content'],
            ],
        );
        $json = $response->json();
        $this->assertSame($json['data'] ?? null, $expected_data);
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
            //User Role ID,                     Allowed?
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
            [ Role::SUBMITTER_ROLE_ID,          false ],
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
        $publication_user_role_id_is_invalid = intval($userRoleId) <= 0;
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
            //User Role                         Allowed?
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
            // User Role ID                     Allowed?
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

    public function testCanUpdatePublicationStyleCriteria()
    {
        $this->beAppAdmin();
        $publication = Publication::factory()->create();
        $criteria = [['name' => 'criteria one', 'description' => 'wonderful criteria', 'icon' => 'icon']];
        $response = $this->graphQL(
            'mutation UpdatePublication($pubId: ID!, $styleCriteria: [CreateStyleCriteriaInput!]) {
                updatePublication(
                    publication: {
                        id: $pubId,
                        style_criterias: {
                            create: $styleCriteria
                        }
                    }
                ) {
                    style_criterias {
                        name
                        description
                        icon
                    }
                }
            }',
            [
                'pubId' => $publication->id,
                'styleCriteria' => $criteria,
            ]
        );

        $response->assertJsonFragment($criteria);
    }

    public function testCanCreatePublicationWithStyleCriteria()
    {
        $this->beAppAdmin();

        $response = $this->graphQL(
            'mutation CreatePublication($styleCriterias: [CreateStyleCriteriaInput!]) {
                createPublication(
                    publication: {
                        name: "Style Critera Pub",
                        style_criterias: {
                            create: $styleCriterias
                        }
                    }
                ) {
                    name
                    style_criterias {
                        id
                        name
                        description
                    }
                }
            }
            ',
            [
                'styleCriterias' => [
                    ['name' => 'Criteria one', 'description' => 'one', 'icon' => 'eye'],
                    ['name' => 'Criteria two', 'description' => 'twp'],
                ],
            ]
        );
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('data.createPublication.style_criterias', 2)
                ->etc());
    }

    public function testCanUpdateExistingStyleCriteria()
    {
        $this->beAppAdmin();
        $publication = Publication::factory()
            ->hasStyleCriterias(3)
            ->create();
        $criteriaId = $publication->styleCriterias[0]->id;
        $response = $this->graphQL(
            'mutation UpdatePublication($publicationId: ID!, $styleCriteria: UpdateStyleCriteriaInput!) {
                updatePublication(
                    publication: {
                        id: $publicationId,
                        style_criterias: {
                            update: [$styleCriteria]
                        }
                    }) {
                        style_criterias {
                            id
                            name
                            description
                            icon
                        }
                    }
                }
            ',
            [
                'styleCriteria' => [
                    'id' => $criteriaId,
                    'name' => 'New Name',
                    'description' => 'new description',
                    'icon' => 'icon',
                ],
                'publicationId' => $publication->id,
            ]
        );
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('data.updatePublication.style_criterias', 3)
                ->has('data.updatePublication.style_criterias.0', fn ($json) =>
                    $json->where('name', 'New Name')
                        ->where('description', 'new description')
                        ->where('id', (string)$criteriaId)
                        ->where('icon', 'icon'))
                        ->etc()
                ->etc());
    }

    public function testCanDeleteStyleCriteria()
    {
        $this->beAppAdmin();

        $publication = Publication::factory()
            ->hasStyleCriterias(2)
            ->create();
        $criteriaId = $publication->styleCriterias[0]->id;

        $response = $this->graphQL(
            'mutation DeleteStyleCriteria($publicationId: ID!, $styleCriteriaId: ID!) {
                updatePublication(
                    publication: {
                        id: $publicationId
                        style_criterias: {
                            delete: [$styleCriteriaId]
                        }
                    })
                    {
                        style_criterias {
                            name
                        }
                    }
                }
            ',
            [
                'publicationId' => $publication->id,
                'styleCriteriaId' => $criteriaId,

            ]
        );
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('data.updatePublication.style_criterias', 1)
            ->etc());
    }

    public function testMaxNumberOfStyleCriteria()
    {
        $this->beAppAdmin();

        $response = $this->graphQL(
            'mutation CreatePublication($criteria: [CreateStyleCriteriaInput!]) {
                createPublication(
                    publication: {
                        name: "Test publication",
                        style_criterias: {
                            create: $criteria
                        }
                    }
                ) {
                    name
                    style_criterias {
                        name
                    }
                }
            }
            ',
            [
                'criteria' => [
                    ['name' => 'one',],
                    ['name' => 'two',],
                    ['name' => 'three',],
                    ['name' => 'four',],
                    ['name' => 'five',],
                    ['name' => 'six',],
                    ['name' => 'seven',],
                ],
            ]
        );
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('errors', 1));
    }

    public function testCannotAddTooManyStyleCriteria()
    {
        $this->beAppAdmin();
        $publication = Publication::factory()->hasStyleCriterias(6)->create();

        $response = $this->graphQL(
            'mutation UpdatePublication($publicationId: ID! $criteria: [CreateStyleCriteriaInput!]) {
                updatePublication(
                    publication: {
                        id: $publicationId
                        style_criterias: {
                            create: $criteria
                        }
                    }
                ) {
                    name
                    style_criterias {
                        name
                    }
                }
            }
            ',
            [
                'publicationId' => $publication->id,
                'criteria' => [
                    ['name' => 'one',],
                    ['name' => 'two',],
                    ['name' => 'three',],
                    ['name' => 'four',],
                    ['name' => 'five',],
                    ['name' => 'six',],
                    ['name' => 'seven',],
                ],
            ]
        );
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('errors', 1)
            ->etc());
    }

    public function testCanUpdateTooManyStyleCriteria()
    {
        $this->beAppAdmin();
        $publication = Publication::factory()->hasStyleCriterias(6)->create();
        $criteriaId = $publication->styleCriterias[0]->id;

        $response = $this->graphQL(
            'mutation UpdatePublication($publicationId: ID! $criteria: [UpdateStyleCriteriaInput!]) {
                updatePublication(
                    publication: {
                        id: $publicationId
                        style_criterias: {
                            update: $criteria
                        }
                    }
                ) {
                    name
                    style_criterias {
                        name
                    }
                }
            }
            ',
            [
                'publicationId' => $publication->id,
                'criteria' => [
                    ['id' => $criteriaId, 'name' => 'one',],
                ],
            ]
        );
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('data.updatePublication.style_criterias.0.name', 'one')
            ->etc());
    }
}
