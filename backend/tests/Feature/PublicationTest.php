<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Publication;
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
            'Generic publication data' => [
                [
                    'name' => 'Test Publication',
                ],
                [
                    'name' => 'Test Publication',
                ],
            ],
            'Name with whitespace' => [
                [
                    'name' => '        Test Publication with Whitespace       ',
                ],
                [
                    'name' => 'Test Publication with Whitespace',
                ],
            ],
            'Name Missing' => [
                [
                    'name' => '',
                ],
                null,
            ],
        ];
    }

    /**
     * @dataProvider publicationMutationProvider
     * @param mixed $data
     * @param mixed $expected Data
     * @return void
     */
    public function testCreation(mixed $data, mixed $expectedData): void
    {
        $this->beAppAdmin();
        $response = $this->graphQL(
            'mutation CreatePublication ($name: String) {
                createPublication(publication:{name: $name}) {
                    name
                }
            }',
            $data
        );

        $response->assertJsonPath('data.createPublication', $expectedData);
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
        $publication = Publication::factory()->create(['is_publicly_visible' => true]);
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
        $this->beAppAdmin();

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

    public function provideCannotAttachUserWithExistingRole()
    {
        return [
            'same role' => ['publicationAdmins', 'publicationAdmins'],
            'different role' => ['publicationAdmins', 'editors'],
        ];
    }

    /**
     * @dataProvider provideCannotAttachUserWithExistingRole
     * @return void
     */
    public function testCannotAttachUserWithExistingRole($existingRole, $newRole)
    {
        $user = User::factory()->create();

        /** @var \App\Models\Publication $publication */
        $publication = Publication::factory()
            ->hasAttached($user, [], $existingRole)
            ->create();

        $this->expectException(QueryException::class);
        $publication->$newRole()->attach($user);
    }

    protected function executePublicationRoleAssignment(string $role, Publication $publication, User $user)
    {
        return $this->graphQL(
            'mutation AssignPublicationRole ($user_id: ID!, $publication_id: ID!) {
                updatePublication(
                    publication: {
                        id: $publication_id
                        ' . $role . ': {
                        connect: [$user_id]
                        }
                    }
                ) {
                    ' . $role . ' {
                        id
                    }
                }
            }',
            [
                'publication_id' => $publication->id,
                'user_id' => $user->id,
            ]
        );
    }

    public function allSubmissionRoles(): array
    {
        return [
            'publication_admins' => ['publication_admins'],
            'editors' => ['editors'],
        ];
    }

    /**
     * @dataProvider allSubmissionRoles
     * @param string $role
     * @return void
     */
    public function testApplicationAdminCanAssignAnyRole(string $role): void
    {
        $this->beAppAdmin();

        $publication = Publication::factory()->create();

        $user = User::factory()->create();

        $response = $this->executePublicationRoleAssignment($role, $publication, $user);

        $response->assertJsonPath("data.updatePublication.$role", [
            [ 'id' => (string)$user->id],
        ]);
    }

    /**
     * @dataProvider allSubmissionRoles
     * @param string $role
     * @return void
     */
    public function testPublicationAdminsCanAssignAnyRole(string $role): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();

        $this->actingAs($admin);

        $publication = Publication::factory()
            ->hasAttached($admin, [], 'publicationAdmins')
            ->create();

        $user = User::factory()->create();

        $response = $this->executePublicationRoleAssignment($role, $publication, $user);

        $response->assertJsonFragment(['id' => (string)$user->id]);
    }

    /**
     * @dataProvider allSubmissionRoles
     * @param string $role
     * @return void
     */
    public function testEditorsCannotAssignAnyRole(string $role): void
    {
        /** @var User $editor */
        $editor = User::factory()->create();

        $this->actingAs($editor);

        $publication = Publication::factory()
            ->hasAttached($editor, [], 'editors')
            ->create();

        $user = User::factory()->create();

        $response = $this->executePublicationRoleAssignment($role, $publication, $user);

        $response->assertJsonPath('data.updatePublication', null);
    }

    public function provideCanUpdatePublicationStyleCriteriaRoles(): array
    {
        return [
            'publicationAdmin' => ['publicationAdmins', true],
            'editors' => ['editors', false],
        ];
    }

    /**
     * @dataProvider provideCanUpdatePublicationStyleCriteriaRoles
     * @return void
     */
    public function testCanUpdatePublicationStyleCriteria(string $role, bool $allowed)
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $publication = Publication::factory()
            ->hasAttached($user, [], $role)
            ->create();

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

        $response->assertJsonPath('data.updatePublication.style_criterias', $allowed ? $criteria : null);
    }

    public function testAdminCanCreatePublicationWithStyleCriteria()
    {
        $this->beAppAdmin();

        $styleCriteria = [
            ['name' => 'Criteria one', 'description' => 'one', 'icon' => 'eye'],
            ['name' => 'Criteria two', 'description' => 'twp', 'icon' => null],
        ];
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
                        name
                        description
                        icon
                    }
                }
            }
            ',
            [
                'styleCriterias' => $styleCriteria,
            ]
        );

        $response->assertJsonPath('data.createPublication.style_criterias', $styleCriteria);
    }

    public function testCanUpdateExistingStyleCriteria()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $publication = Publication::factory()
            ->hasStyleCriterias(3)
            ->hasAttached($user, [], 'publicationAdmins')
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
        $response->assertJsonCount(3, 'data.updatePublication.style_criterias');
        $response->assertJsonPath('data.updatePublication.style_criterias.0', [
            'id' => (string)$criteriaId,
            'name' => 'New Name',
            'description' => 'new description',
            'icon' => 'icon',
        ]);
    }

    public function testCanDeleteStyleCriteria()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $publication = Publication::factory()
            ->hasStyleCriterias(2)
            ->hasAttached($user, [], 'publicationAdmins')
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
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $publication = Publication::factory()
            ->hasStyleCriterias(6)
            ->hasAttached($user, [], 'publicationAdmins')
            ->create();

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
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $publication = Publication::factory()
            ->hasStyleCriterias(6)
            ->hasAttached($user, [], 'publicationAdmins')
            ->create();

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
