<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\ApiTestCase;

class PublicationTest extends ApiTestCase
{
    use RefreshDatabase;

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
                    'home_page_content' => '<div class="Example">Div content</div>
                                            <a href="http://example">Example link</a>
                                            <b>Bold content</b>
                                            <i>Italics content</i>
                                            <u>Underlined content</u>
                                            <p>Paragraph content</p>
                                            <span>Itema with a break <br /></span>
                                            <ol><li>List item 1</li><li>List item 2</li></ol>
                                            <ul><li>List item 1</li><li>List item 2</li></ul>',
                    'new_submission_content' => '<div class="Example">Div content</div>
                                            <a href="http://example">Example link</a>
                                            <b>Bold content</b>
                                            <i>Italics content</i>
                                            <u>Underlined content</u>
                                            <p>Paragraph content</p>
                                            <span>Itema with a break <br /></span>
                                            <ol><li>List item 1</li><li>List item 2</li></ol>
                                            <ul><li>List item 1</li><li>List item 2</li></ul>',
                ],
                [
                    'createPublication' => [
                        'name' => 'Test Publication',
                        'home_page_content' => '<div>Div content</div>
                                            <a href="http://example">Example link</a>
                                            <b>Bold content</b>
                                            <i>Italics content</i>
                                            <u>Underlined content</u>
                                            <p>Paragraph content</p>
                                            Itema with a break <br />
                                            <ol><li>List item 1</li><li>List item 2</li></ol>
                                            <ul><li>List item 1</li><li>List item 2</li></ul>',
                        'new_submission_content' => '<div>Div content</div>
                                            <a href="http://example">Example link</a>
                                            <b>Bold content</b>
                                            <i>Italics content</i>
                                            <u>Underlined content</u>
                                            <p>Paragraph content</p>
                                            Itema with a break <br />
                                            <ol><li>List item 1</li><li>List item 2</li></ol>
                                            <ul><li>List item 1</li><li>List item 2</li></ul>',
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
     * @return array
     */
    public function publicationContentUpdateProvider(): array
    {
        return [
            [
                [
                    'name' => 'Test Publication',
                    'home_page_content' => 'Amet animi quaerat eum sint placeat aut ratione iure. Quod dolor esse et. Error et tempora ipsa eum eos sequi facilis. A ipsam enim ullam minima. | Aut quam repellat ut nemo qui rerum quam. Veniam aut amet ullam nam eum odit laboriosam. Praesentium nulla similique omnis sed dolor. Et impedit quasi odit veritatis.',
                    'new_submission_content' => 'Voluptatem nam quidem perspiciatis. Qui sed quis harum aut porro maxime. Illo ipsa sint nobis repudiandae a voluptatem. Aut nostrum sunt soluta possimus.',
                ],
                [
                    'updatePublication' => [
                        'name' => 'Test Publication',
                        'home_page_content' => 'Amet animi quaerat eum sint placeat aut ratione iure. Quod dolor esse et. Error et tempora ipsa eum eos sequi facilis. A ipsam enim ullam minima. | Aut quam repellat ut nemo qui rerum quam. Veniam aut amet ullam nam eum odit laboriosam. Praesentium nulla similique omnis sed dolor. Et impedit quasi odit veritatis.',
                        'new_submission_content' => 'Voluptatem nam quidem perspiciatis. Qui sed quis harum aut porro maxime. Illo ipsa sint nobis repudiandae a voluptatem. Aut nostrum sunt soluta possimus.',
                    ],
                ],
            ],
            [
                [
                    'name' => 'Test Publication',
                    'home_page_content' => '<div class="Example">Div content</div>
                                            <a href="http://example">Example link</a>
                                            <b>Bold content</b>
                                            <i>Italics content</i>
                                            <u>Underlined content</u>
                                            <p>Paragraph content</p>
                                            <span>Itema with a break <br /></span>
                                            <ol><li>List item 1</li><li>List item 2</li></ol>
                                            <ul><li>List item 1</li><li>List item 2</li></ul>',
                    'new_submission_content' => '<div class="Example">Div content</div>
                                            <a href="http://example">Example link</a>
                                            <b>Bold content</b>
                                            <i>Italics content</i>
                                            <u>Underlined content</u>
                                            <p>Paragraph content</p>
                                            <span>Itema with a break <br /></span>
                                            <ol><li>List item 1</li><li>List item 2</li></ol>
                                            <ul><li>List item 1</li><li>List item 2</li></ul>',
                ],
                [
                    'updatePublication' => [
                        'name' => 'Test Publication',
                        'home_page_content' => '<div>Div content</div>
                                            <a href="http://example">Example link</a>
                                            <b>Bold content</b>
                                            <i>Italics content</i>
                                            <u>Underlined content</u>
                                            <p>Paragraph content</p>
                                            Itema with a break <br />
                                            <ol><li>List item 1</li><li>List item 2</li></ol>
                                            <ul><li>List item 1</li><li>List item 2</li></ul>',
                        'new_submission_content' => '<div>Div content</div>
                                            <a href="http://example">Example link</a>
                                            <b>Bold content</b>
                                            <i>Italics content</i>
                                            <u>Underlined content</u>
                                            <p>Paragraph content</p>
                                            Itema with a break <br />
                                            <ol><li>List item 1</li><li>List item 2</li></ol>
                                            <ul><li>List item 1</li><li>List item 2</li></ul>',
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
                    'updatePublication' => [
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
        $this->beAppAdmin();
        $response = $this->graphQL(
            'mutation CreatePublication ($publication_name: String, $home_page_content: String, $new_submission_content: String) {
                createPublication(publication: {
                    name: $publication_name
                    home_page_content: $home_page_content
                    new_submission_content: $new_submission_content
                }) {
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
     * @dataProvider publicationContentUpdateProvider
     * @return void
     */
    public function testContentUpdate(mixed $publication_data, mixed $expected_data): void
    {
        $this->beAppAdmin();
        $user = User::factory()->create();

        $publication = Publication::factory()
            ->hasAttached($user, [], 'publicationAdmins')
            ->create();

        $response = $this->graphQL(
            'mutation UpdatePublication ($pubId: ID!, $publication_name: String, $home_page_content: String, $new_submission_content: String) {
                updatePublication(
                    publication: {
                        id: $pubId,
                        name: $publication_name
                        home_page_content: $home_page_content
                        new_submission_content: $new_submission_content
                    }
                ) {
                    name,
                    home_page_content,
                    new_submission_content,
                }
            }',
            [
                'pubId' => $publication->id,
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

    public function allPublicationRoles(): array
    {
        return [
            'publication_admins' => ['publication_admins'],
            'editors' => ['editors'],
        ];
    }

    /**
     * @dataProvider allPublicationRoles
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
     * @dataProvider allPublicationRoles
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
     * @dataProvider allPublicationRoles
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

    /**
     * @dataProvider allPublicationRoles
     * @param string $role
     * @return void
     */
    public function testMyRoleFields(string $role): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        $publication = Publication::factory()->create();
        $camelized = Str::camel($role);
        $publication->$camelized()->attach($user);
        $graphql = '
            query GetPublication($id: ID!) {
                publication(id: $id) {
                    my_role
                    effective_role
                }
            }
        ';

        $response = $this->graphQL($graphql, ['id' => $publication->id]);
        $response
            ->assertJsonPath('data.publication.my_role', Str::singular($role))
            ->assertJsonPath('data.publication.effective_role', Str::singular($role));
    }

    public function testAdminGetsEffectiveRole()
    {
        $this->beAppAdmin();
        $publication = Publication::factory()->create();
        $gql = '
            query GetPublication($id: ID!) {
                publication(id: $id) {
                    my_role
                    effective_role
                }
            }
        ';

        $this->graphQL($gql, ['id' => $publication->id])
            ->assertJsonPath('data.publication.my_role', null)
            ->assertJsonPath('data.publication.effective_role', 'publication_admin');
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
