<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\ApiTestCase;

class PublicationTest extends ApiTestCase
{
    use RefreshDatabase;

    /**
     * @return array
     */
    public static function publicationContentMutationProvider(): array
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
    public static function publicationContentUpdateProvider(): array
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

    #[DataProvider('publicationContentMutationProvider')]
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

    #[DataProvider('publicationContentUpdateProvider')]
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
    public static function publicationMutationProvider(): array
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
     * @return array
     */
    public static function publicationAcceptSubmissionsUpdateProvider(): array
    {
        return [
            'Publication Administrator Can Update Setting to Accept Submissions' => [
                'publicationAdmins',
                true,
                true,
            ],
            'Publication Administrator Can Update Setting to Reject Submissions' => [
                'publicationAdmins',
                false,
                false,
            ],
        ];
    }

    /**
     * @param string $role
     * @param bool $update_value
     * @param bool $expected_value
     * @return void
     */
    #[DataProvider('publicationAcceptSubmissionsUpdateProvider')]
    public function testUserCanUpdateAcceptSubmissionsSetting(string $role, bool $update_value, bool $expected_value): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $publication = Publication::factory()
            ->hasAttached($user, [], $role)
            ->create();

        $response = $this->graphQL(
            'mutation UpdatePublication($pubId: ID!, $isAcceptingSubmissions: Boolean) {
             updatePublication(
                 publication: {
                     id: $pubId,
                     is_accepting_submissions: $isAcceptingSubmissions
                 }
             ) {
                is_accepting_submissions
             }
         }',
            [
                'pubId' => $publication->id,
                'isAcceptingSubmissions' => $update_value,
            ]
        );
        $response->assertJsonPath('data.updatePublication.is_accepting_submissions', $expected_value);
    }

    /**
     * @param mixed $data
     * @param mixed $expected Data
     * @return void
     */
    #[DataProvider('publicationMutationProvider')]
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
            ['id' => $publication->id]
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
                publications(public: true) {
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

    public function testUnassignedUserCannotSeeHiddenPublication()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        Publication::factory()->count(2)->create();
        Publication::factory()->hidden()->count(2)->create();
        $response = $this->graphQL(
            'query GetPublications {
                publications {
                    data {
                        id
                    }
                }
            }'
        );
        $json = $response->json('data.publications.data');

        $this->assertCount(2, $json);
    }

    public function testAssignedUserCanSeeHiddenPublication()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        Publication::factory()->hidden()->count(2)->create();
        Publication::factory()->hidden()
            ->hasAttached($user, [], 'editors')
            ->create();

        $response = $this->graphQL(
            'query GetPublications {
                publications {
                    data {
                        id
                    }
                }
            }'
        );
        $json = $response->json('data.publications.data');

        $this->assertCount(1, $json);
    }

    /**
     * Pins the search behavior hardening:
     * - Terms shorter than MIN_SEARCH_LENGTH are short-circuited (no
     *   filtering, full list returned).
     * - `%` / `_` in the user's term are treated as literals, not LIKE
     *   wildcards (so the `_` in "a_" doesn't match "aa").
     */
    public function testPublicationSearchMinLengthAndWildcardEscape()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        Publication::factory()->create(['name' => 'Alpha Quarterly']);
        Publication::factory()->create(['name' => 'Beta Review']);
        Publication::factory()->create(['name' => 'A%Something']);

        // Short term — not enforced, all three returned.
        $short = $this->graphQL(
            'query { publications(search: "Al") { data { name } } }'
        );
        $this->assertCount(3, $short->json('data.publications.data'));

        // Long enough — narrows.
        $long = $this->graphQL(
            'query { publications(search: "Alpha") { data { name } } }'
        );
        $this->assertCount(1, $long->json('data.publications.data'));
        $this->assertSame(
            'Alpha Quarterly',
            $long->json('data.publications.data.0.name')
        );

        // The user's `%` must be escaped — should match literally, not act
        // as a wildcard. "A%S" matches only the "A%Something" row, not
        // "Alpha Something"-like names that would match under an un-escaped
        // `%`.
        $wildcard = $this->graphQL(
            'query { publications(search: "A%S") { data { name } } }'
        );
        $this->assertCount(1, $wildcard->json('data.publications.data'));
        $this->assertSame(
            'A%Something',
            $wildcard->json('data.publications.data.0.name')
        );
    }

    /**
     * Pins the `with_statuses` filter on `User.publications`: only
     * assignments whose publication currently has at least one
     * submission in one of the given statuses come back. Drafts
     * never count.
     */
    public function testUserPublicationsFilterByWithStatuses()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // Three publications, all assigned to the user as editor.
        $screening = Publication::factory()
            ->hasAttached($user, [], 'editors')
            ->create(['name' => 'Screening Pub']);
        Submission::factory()->for($screening)->create([
            'status' => Submission::INITIALLY_SUBMITTED,
        ]);

        $reviewing = Publication::factory()
            ->hasAttached($user, [], 'editors')
            ->create(['name' => 'Reviewing Pub']);
        Submission::factory()->for($reviewing)->create([
            'status' => Submission::UNDER_REVIEW,
        ]);

        // Has only a draft submission — must not match any status filter.
        $draftsOnly = Publication::factory()
            ->hasAttached($user, [], 'editors')
            ->create(['name' => 'Drafts Only Pub']);
        Submission::factory()->for($draftsOnly)->create([
            'status' => Submission::DRAFT,
        ]);

        $response = $this->graphQL(
            'query ($statuses: [SubmissionStatus!]) {
                currentUser {
                    publications(with_statuses: $statuses) {
                        data {
                            publication { id name }
                        }
                    }
                }
            }',
            ['statuses' => ['INITIALLY_SUBMITTED']]
        );

        $rows = $response->json('data.currentUser.publications.data');
        $this->assertCount(1, $rows);
        $this->assertSame('Screening Pub', $rows[0]['publication']['name']);

        // Null/empty filter returns every assignment (minus the draft-only check).
        $allResponse = $this->graphQL(
            'query {
                currentUser {
                    publications { data { publication { id } } }
                }
            }'
        );
        $this->assertCount(
            3,
            $allResponse->json('data.currentUser.publications.data')
        );
    }

    /**
     * Pins the fix for the OR-precedence bug in `visible()`: the
     * public/assigned disjunction must be grouped so that downstream
     * filters (search, my_role, etc.) narrow the result set instead
     * of being short-circuited by the "publicly visible" branch.
     */
    public function testSearchFilterAppliesAcrossPublicAndAssigned()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // Public, matches the search.
        Publication::factory()->create(['name' => 'Digital Humanities Review']);
        // Public, doesn't match.
        Publication::factory()->create(['name' => 'Quarterly Review']);
        // Hidden + assigned to the user, doesn't match — must not leak in.
        Publication::factory()->hidden()
            ->hasAttached($user, [], 'editors')
            ->create(['name' => 'Private Journal']);

        $response = $this->graphQL(
            'query ($search: String) {
                publications(search: $search) {
                    data { id name }
                }
            }',
            ['search' => 'Digital']
        );
        $json = $response->json('data.publications.data');

        $this->assertCount(1, $json);
        $this->assertSame('Digital Humanities Review', $json[0]['name']);
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

    public static function publicationRolesProvider(): array
    {
        return [
            'publication_admins' => ['publication_admins'],
            'editors' => ['editors'],
        ];
    }

    /**
     * @param string $role
     * @return void
     */
    #[DataProvider('publicationRolesProvider')]
    public function testApplicationAdminCanAssignAnyRole(string $role): void
    {
        $this->beAppAdmin();

        $publication = Publication::factory()->create();

        $user = User::factory()->create();

        $response = $this->executePublicationRoleAssignment($role, $publication, $user);

        $response->assertJsonPath("data.updatePublication.$role", [
            ['id' => (string)$user->id],
        ]);
    }

    /**
     * @param string $role
     * @return void
     */
    #[DataProvider('publicationRolesProvider')]
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
     * @param string $role
     * @return void
     */
    #[DataProvider('publicationRolesProvider')]
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
     * @param string $role
     * @return void
     */
    #[DataProvider('publicationRolesProvider')]
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

    /**
     * @return array
     */
    public static function publicationStyleCriteriaUpdateRoles(): array
    {
        return [
            'publicationAdmin' => ['publicationAdmins', true],
            'editors' => ['editors', false],
        ];
    }

    /**
     * @return void
     */
    #[DataProvider('publicationStyleCriteriaUpdateRoles')]
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
        $response->assertJson(fn(AssertableJson $json) => $json->has('data.updatePublication.style_criterias', 1)
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
        $response->assertJson(fn(AssertableJson $json) => $json->has('errors', 1));
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
        $response->assertJson(fn(AssertableJson $json) => $json->has('errors', 1)
            ->etc());
    }

    private const DASHBOARD_QUERY = '
        query ($id: ID!) {
            publication(id: $id) {
                submissions(first: 10) {
                    paginatorInfo { total }
                    data { id title status }
                }
                submission_status_counts { status count }
            }
        }
    ';

    public function testEditorCanQueryDashboardSubmissions(): void
    {
        /** @var User $editor */
        $editor = User::factory()->create();
        $this->actingAs($editor);

        $publication = Publication::factory()
            ->hasAttached($editor, [], 'editors')
            ->create();

        $submitter = User::factory()->create();
        Submission::factory()
            ->for($publication)
            ->hasAttached($submitter, [], 'submitters')
            ->create(['status' => Submission::INITIALLY_SUBMITTED]);

        $response = $this->graphQL(self::DASHBOARD_QUERY, [
            'id' => (string)$publication->id,
        ]);

        $response->assertJsonPath('data.publication.submissions.paginatorInfo.total', 1);
        $this->assertNotEmpty($response->json('data.publication.submission_status_counts'));
    }

    public function testPublicationAdminCanQueryDashboardSubmissions(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $publication = Publication::factory()
            ->hasAttached($admin, [], 'publicationAdmins')
            ->create();

        $submitter = User::factory()->create();
        Submission::factory()
            ->for($publication)
            ->hasAttached($submitter, [], 'submitters')
            ->create(['status' => Submission::UNDER_REVIEW]);

        $response = $this->graphQL(self::DASHBOARD_QUERY, [
            'id' => (string)$publication->id,
        ]);

        $response->assertJsonPath('data.publication.submissions.paginatorInfo.total', 1);
    }

    public function testOutsiderCannotQueryHiddenPublicationDashboard(): void
    {
        $owner = User::factory()->create();
        $publication = Publication::factory()
            ->hidden()
            ->hasAttached($owner, [], 'publicationAdmins')
            ->create();

        $submitter = User::factory()->create();
        Submission::factory()
            ->for($publication)
            ->hasAttached($submitter, [], 'submitters')
            ->create(['status' => Submission::INITIALLY_SUBMITTED]);

        /** @var User $outsider */
        $outsider = User::factory()->create();
        $this->actingAs($outsider);

        $response = $this->graphQL(self::DASHBOARD_QUERY, [
            'id' => (string)$publication->id,
        ]);

        $response->assertJsonPath('data.publication', null);
    }

    public function testOutsiderCannotQueryPublicPublicationDashboard(): void
    {
        // The `view` policy is intentionally permissive for public
        // publications — anyone can resolve `publication(id: ...)`
        // to read basic metadata. The nested dashboard fields
        // (submissions + submission_status_counts) must not piggyback
        // on that permission; they expose in-flight submissions and
        // aggregate operational metrics that shouldn't leak from
        // public publications.
        $owner = User::factory()->create();
        $publication = Publication::factory()
            ->hasAttached($owner, [], 'publicationAdmins')
            ->create(['is_publicly_visible' => true]);

        $submitter = User::factory()->create();
        Submission::factory()
            ->for($publication)
            ->hasAttached($submitter, [], 'submitters')
            ->create(['status' => Submission::INITIALLY_SUBMITTED]);

        /** @var User $outsider */
        $outsider = User::factory()->create();
        $this->actingAs($outsider);

        $response = $this->graphQL(self::DASHBOARD_QUERY, [
            'id' => (string)$publication->id,
        ]);

        // Both nested fields are non-null in the schema, so when the
        // guard throws the GraphQL error bubbles up to the nearest
        // nullable parent — here that's `publication` itself. End
        // result: outsiders see a null publication + an auth error,
        // same as the hidden-publication case.
        $response->assertJsonPath('data.publication', null);
        $response->assertJsonStructure(['errors']);
    }

    public function testDashboardExcludesDraftSubmissions(): void
    {
        /** @var User $editor */
        $editor = User::factory()->create();
        $this->actingAs($editor);

        $publication = Publication::factory()
            ->hasAttached($editor, [], 'editors')
            ->create();

        $submitter = User::factory()->create();

        Submission::factory()
            ->for($publication)
            ->hasAttached($submitter, [], 'submitters')
            ->create(['status' => Submission::DRAFT]);

        Submission::factory()
            ->for($publication)
            ->hasAttached($submitter, [], 'submitters')
            ->create(['status' => Submission::INITIALLY_SUBMITTED]);

        $response = $this->graphQL(self::DASHBOARD_QUERY, [
            'id' => (string)$publication->id,
        ]);

        // Only the non-draft submission should appear.
        $response->assertJsonPath('data.publication.submissions.paginatorInfo.total', 1);

        // Status counts should also exclude DRAFT.
        $counts = collect($response->json('data.publication.submission_status_counts'));
        $this->assertNull($counts->firstWhere('status', 'DRAFT'));
        $this->assertEquals(1, $counts->sum('count'));
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
        $response->assertJson(fn(AssertableJson $json) => $json->where('data.updatePublication.style_criterias.0.name', 'one')
            ->etc());
    }
}
