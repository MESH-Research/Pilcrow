<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\Role;
use App\Models\Submission;
use App\Models\SubmissionUser;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\Fluent\AssertableJson;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testSubmissionsHaveAOneToManyRelationshipWithPublications()
    {
        $publication = Publication::factory()->create([
            'name' => 'Test Publication #1',
        ]);
        Publication::factory()->count(5)->create();
        $submissions = Submission::factory()->count(10)->for($publication)->create();
        Submission::factory()->count(16)->create();
        $this->assertEquals(1, $submissions->pluck('publication_id')->unique()->count());
        $this->assertEquals(10, $publication->submissions->count());
    }

    /**
     * @return void
     */
    public function testSubmissionsHaveAManyToManyRelationshipWithUsers()
    {
        $submission_count = 100;
        $user_count = 50;
        $publication = Publication::factory()->create([
            'name' => 'Test Publication #2',
        ]);
        $users = User::factory()->count($user_count)->create();

        // Create submissions and attach them to users randomly with random roles
        for ($i = 0; $i < $submission_count; $i++) {
            $random_user = $users->random();
            $random_role_id = Role::whereIn(
                'name',
                [
                    Role::REVIEW_COORDINATOR,
                    Role::REVIEWER,
                    Role::SUBMITTER,
                ]
            )
                ->get()
                ->pluck('id')
                ->random();
            $submission = Submission::factory()->hasAttached(
                $random_user,
                [
                    'role_id' => $random_role_id,
                ]
            )
                ->for($publication)
                ->create();

            // Ensure at least one submitter is attached if one was not previously attached
            if ($random_role_id !== Role::SUBMITTER_ROLE_ID) {
                $random_non_duplicate_user = $users->reject(function ($user) use ($random_user) {
                    return $user->id === $random_user->id;
                })->random();
                $submission->users()->attach(
                    $random_non_duplicate_user,
                    [
                        'role_id' => Role::SUBMITTER_ROLE_ID,
                    ]
                );
            }
        }
        Submission::all()->map(function ($submission) {
            $this->assertGreaterThan(0, $submission->users->count());
            $this->assertLessThanOrEqual(2, $submission->users->count());
            $submission->users->map(function ($user) {
                $this->assertIsInt(
                    User::where(
                        'id',
                        $user->id
                    )->firstOrFail()->id
                );
            });
        });
        User::all()->map(function ($user) use ($submission_count) {
            $this->assertLessThanOrEqual($submission_count, $user->submissions->count());
            $user->submissions->map(function ($submission) {
                $this->assertIsInt(
                    Submission::where(
                        'id',
                        $submission->id
                    )->firstOrFail()->id
                );
            });
        });
    }

    /**
     * @return void
     */
    public function testIndividualSubmissionsCanBeQueriedById()
    {
        $submission = Submission::factory()->create([
            'title' => 'Test Submission #1 for Querying an Individual Submission',
        ]);
        $response = $this->graphQL(
            'query GetSubmission($id: ID!) {
                submission (id: $id) {
                    id
                    title
                }
            }',
            [ 'id' => $submission->id ]
        );
        $expected_data = [
            'submission' => [
                'id' => (string)$submission->id,
                'title' => 'Test Submission #1 for Querying an Individual Submission',
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testAllSubmissionsCanBeQueried()
    {
        $submission_1 = Submission::factory()->create([
            'title' => 'Test Submission #2 for Querying All Submissions',
        ]);
        $submission_2 = Submission::factory()->create([
            'title' => 'Test Submission #3 for Querying All Submissions',
        ]);
        $response = $this->graphQL(
            'query GetSubmissions {
                submissions {
                    data {
                        id
                        title
                    }
                }
            }'
        );
        $expected_data = [
            'submissions' => [
                'data' => [
                    [
                        'id' => (string)$submission_1->id,
                        'title' => 'Test Submission #2 for Querying All Submissions',
                    ],
                    [
                        'id' => (string)$submission_2->id,
                        'title' => 'Test Submission #3 for Querying All Submissions',
                    ],
                ],
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testSubmissionsCanBeQueriedForAPublication()
    {
        $publication = Publication::factory()->create([
            'name' => 'Test Publication #3',
        ]);
        $submission = Submission::factory()->hasAttached(
            User::factory()->create(),
            [
                'role_id' => Role::SUBMITTER_ROLE_ID,
            ]
        )
            ->for($publication)
            ->create([
                'title' => 'Test Submission #4 for Publication #3',
            ]);
        $response = $this->graphQL(
            'query GetSubmissionsByPublication($id: ID!) {
                publication (id: $id) {
                    id
                    name
                    submissions {
                        id
                        title
                    }
                }
            }',
            [ 'id' => $publication->id ]
        );
        $expected_data = [
            'publication' => [
                'id' => (string)$publication->id,
                'name' => 'Test Publication #3',
                'submissions' => [
                    [
                        'id' => (string)$submission->id,
                        'title' => 'Test Submission #4 for Publication #3',
                    ],
                ],
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testSubmissionsCanBeQueriedForAUser()
    {
        $publication = Publication::factory()->create([
            'name' => 'Test Publication #4',
        ]);
        $user = User::factory()->create([
            'name' => 'Test User #1 With Submission',
        ]);
        $submission = Submission::factory()->hasAttached(
            $user,
            [
                'role_id' => Role::SUBMITTER_ROLE_ID,
            ]
        )
            ->for($publication)
            ->create([
                'title' => 'Test Submission #5 for Test User #1 With Submission',
            ]);

        $response = $this->graphQL(
            'query GetSubmissionsByUser($id: ID!) {
                user (id: $id) {
                    id
                    name
                    submissions {
                        id
                        title
                        pivot {
                            role_id
                        }
                    }
                }
            }',
            [ 'id' => $user->id ]
        );
        $expected_data = [
            'user' => [
                'id' => (string)$user->id,
                'name' => 'Test User #1 With Submission',
                'submissions' => [
                    [
                        'id' => (string)$submission->id,
                        'title' => 'Test Submission #5 for Test User #1 With Submission',
                        'pivot' => [
                            'role_id' => Role::SUBMITTER_ROLE_ID,
                        ],
                    ],
                ],
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testUsersCanBeQueriedForASubmission()
    {
        $publication = Publication::factory()->create([
            'name' => 'Test Publication #5',
        ]);
        $user = User::factory()->create([
            'name' => 'Test User #2 With Submission',
        ]);
        $submission = Submission::factory()->hasAttached(
            $user,
            [
                'role_id' => Role::SUBMITTER_ROLE_ID,
            ]
        )
            ->for($publication)
            ->create([
                'title' => 'Test Submission #6 for Test User #2 With Submission',
            ]);
        $response = $this->graphQL(
            'query GetUsersBySubmission($id: ID!) {
                submission (id: $id) {
                    id
                    title
                    users {
                        id
                        name
                        pivot {
                            role_id
                        }
                    }
                }
            }',
            [ 'id' => $submission->id ]
        );
        $expected_data = [
            'submission' => [
                'id' => (string)$submission->id,
                'title' => 'Test Submission #6 for Test User #2 With Submission',
                'users' => [
                    [
                        'id' => (string)$user->id,
                        'name' => 'Test User #2 With Submission',
                        'pivot' => [
                            'role_id' => Role::SUBMITTER_ROLE_ID,
                        ],
                    ],
                ],
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return array
     */
    public function createSubmissionMutationProvider(): array
    {
        return [
            [
                [
                    'publication_name' => 'Test Publication for Submission Creation in PHPUnit Via Mutation',
                    'submission_title' => 'Test Submission Created in PHPUnit Via Mutation',
                    'expected_data' => [
                        'createSubmission' => [
                            'title' => 'Test Submission Created in PHPUnit Via Mutation',
                            'publication' => [
                                'name' => 'Test Publication for Submission Creation in PHPUnit Via Mutation',
                            ],
                        ],
                    ],
                ],
            ],
            [
                [
                    'publication_name' => 'Test Publication for Submission with Whitespace Creation in PHPUnit Via Mutation',
                    'submission_title' => '        Test Submission with Whitespace Created in PHPUnit Via Mutation       ',
                    'expected_data' => [
                        'createSubmission' => [
                            'title' => 'Test Submission with Whitespace Created in PHPUnit Via Mutation',
                            'publication' => [
                                'name' => 'Test Publication for Submission with Whitespace Creation in PHPUnit Via Mutation',
                            ],
                        ],
                    ],
                ],
            ],
            [
                [
                    'publication_name' => '',
                    'submission_title' => '',
                    'expected_data' => null,
                ],
            ],
        ];
    }

    /**
     * @dataProvider createSubmissionMutationProvider
     * @return void
     */
    public function testSubmissionCreationViaMutation(array $case)
    {
        $publication = Publication::factory()->create([
            'name' => $case['publication_name'],
        ]);
        $user = User::factory()->create();
        $operations = [
            'operationName' => 'CreateSubmission',
            'query' => '
                mutation CreateSubmission (
                    $title: String!
                    $publication_id: ID!
                    $submitter_user_id: ID!
                    $file_upload: [Upload!]
                ) {
                    createSubmission(
                        input: {
                            title: $title,
                            publication_id: $publication_id,
                            users: { connect: [{ id: $submitter_user_id, role_id: ' . Role::SUBMITTER_ROLE_ID . ' }] }
                            files: { create: $file_upload }
                        }
                    ) {
                        title
                        publication {
                            name
                        }
                    }
                }
            ',
            'variables' => [
                'title' => $case['submission_title'],
                'publication_id' => $publication->id,
                'submitter_user_id' => $user->id,
                'file_upload' => null,
            ],
        ];
        $map = [
            '0' => ['variables.file_upload'],
        ];
        $file = [
            '0' => UploadedFile::fake()->create('test.txt', 500),
        ];
        $this->multipartGraphQL($operations, $map, $file)
            ->assertJsonPath('data', $case['expected_data']);
    }

    /**
     * @return void
     */
    public function testUserRoleAndUserAreUniqueForASubmission()
    {
        $user = User::factory()->create();
        $role_id = Role::where('name', Role::REVIEW_COORDINATOR)->first()->id;

        $submission = Submission::factory()->hasAttached(
            $user,
            [
                'role_id' => $role_id,
            ]
        )
            ->create();
        $this->expectException(QueryException::class);
        $submission->users()->attach(
            $user,
            [
                'role_id' => $role_id,
            ]
        );
        $submission_pivot_data = SubmissionUser::where(
            [
                'user_id' => $user->id,
                'role_id' => $role_id,
                'submission_id' => $submission->id,
            ]
        )
            ->get();
        $this->assertEquals(1, $submission_pivot_data->count());
    }

    /**
     * @return array
     */
    public function createSubmissionUserViaMutationAsAnApplicationAdministratorProvider(): array
    {
        return [
            [
                [
                    'submission_user_role_id' => Role::SUBMITTER_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::REVIEWER_ROLE_ID,
                    'allowed' => true,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::REVIEW_COORDINATOR_ROLE_ID,
                    'allowed' => true,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::EDITOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::PUBLICATION_ADMINISTRATOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::APPLICATION_ADMINISTRATOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => 0,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => '',
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => null,
                    'allowed' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider createSubmissionUserViaMutationAsAnApplicationAdministratorProvider
     * @return void
     */
    public function testCreateSubmissionUserViaMutationAsAnApplicationAdministrator(array $case)
    {
        /** @var User $administrator */
        $administrator = User::factory()->create();
        $administrator->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->actingAs($administrator);
        $publication = Publication::factory()->create();
        $submitter = User::factory()->create();
        $submission = Submission::factory()
            ->for($publication)
            ->hasAttached($submitter, ['role_id' => Role::SUBMITTER_ROLE_ID])
            ->create([
                'title' => 'Test Submission for Test User With Submission',
            ]);
        $user_to_be_assigned = User::factory()->create();
        $mutation_response = $this->graphQL(
            'mutation CreateSubmissionUser ($role_id: ID!, $submission_id: ID!, $user_id: ID!) {
                createSubmissionUser(
                    submission_user: { role_id: $role_id, submission_id: $submission_id, user_id: $user_id }
                ) {
                    role_id
                    submission_id
                    user_id
                }
            }',
            [
                'role_id' => $case['submission_user_role_id'],
                'submission_id' => $submission->id,
                'user_id' => $user_to_be_assigned->id,
            ]
        );
        $expected_mutation_response = null;
        if ($case['allowed']) {
            $expected_mutation_response = [
                'createSubmissionUser' => [
                    'role_id' => $case['submission_user_role_id'],
                    'submission_id' => (string)$submission->id,
                    'user_id' => (string)$user_to_be_assigned->id,
                ],
            ];
        }
        $mutation_response->assertJsonPath('data', $expected_mutation_response);
        $query_response = $this->graphQL(
            'query GetSubmission ($id: ID!) {
                submission( id: $id ) {
                    users {
                        id
                        pivot {
                            role_id
                        }
                    }
                }
            }',
            [
                'id' => $submission->id,
            ]
        );
        $expected_query_response = [
            'submission' => [
                'users' => [
                    [
                        'id' => (string)$submitter->id,
                        'pivot' => [
                            'role_id' => Role::SUBMITTER_ROLE_ID,
                        ],
                    ],
                ],
            ],
        ];
        if ($case['allowed']) {
            array_push(
                $expected_query_response['submission']['users'],
                [
                    'id' => (string)$user_to_be_assigned->id,
                    'pivot' => [
                        'role_id' => $case['submission_user_role_id'],
                    ],
                ],
            );
        }
        $query_response->assertJsonPath('data', $expected_query_response);
    }

    /**
     * @return array
     */
    public function createSubmissionUserViaMutationAsAnEditorProvider(): array
    {
        return [
            [
                [
                    'submission_user_role_id' => Role::SUBMITTER_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::REVIEWER_ROLE_ID,
                    'allowed' => true,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::REVIEW_COORDINATOR_ROLE_ID,
                    'allowed' => true,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::EDITOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::PUBLICATION_ADMINISTRATOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::APPLICATION_ADMINISTRATOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => 0,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => '',
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => null,
                    'allowed' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider createSubmissionUserViaMutationAsAnEditorProvider
     * @return void
     */
    public function testCreateSubmissionUserViaMutationAsAnEditor(array $case)
    {
        /** @var User $editor */
        $editor = User::factory()->create();
        $editor->assignRole(Role::EDITOR);
        $this->actingAs($editor);
        $user_to_be_assigned = User::factory()->create();
        $publication = Publication::factory()->create();
        $submitter = User::factory()->create();
        $submission = Submission::factory()
            ->for($publication)
            ->hasAttached($submitter, ['role_id' => Role::SUBMITTER_ROLE_ID])
            ->create([
                'title' => 'Test Submission for Test User With Submission',
            ]);
        $mutation_response = $this->graphQL(
            'mutation CreateSubmissionUser ($role_id: ID!, $submission_id: ID!, $user_id: ID!) {
                createSubmissionUser(
                    submission_user: { role_id: $role_id, submission_id: $submission_id, user_id: $user_id }
                ) {
                    role_id
                    submission_id
                    user_id
                }
            }',
            [
                'role_id' => $case['submission_user_role_id'],
                'submission_id' => $submission->id,
                'user_id' => $user_to_be_assigned->id,
            ]
        );
        $expected_mutation_response = null;
        if ($case['allowed']) {
            $expected_mutation_response = [
                'createSubmissionUser' => [
                    'role_id' => $case['submission_user_role_id'],
                    'submission_id' => (string)$submission->id,
                    'user_id' => (string)$user_to_be_assigned->id,
                ],
            ];
        }
        $mutation_response->assertJsonPath('data', $expected_mutation_response);
        $query_response = $this->graphQL(
            'query GetSubmission ($id: ID!) {
                submission( id: $id ) {
                    users {
                        id
                        pivot {
                            role_id
                        }
                    }
                }
            }',
            [
                'id' => $submission->id,
            ]
        );
        $expected_query_response = [
            'submission' => [
                'users' => [
                    [
                        'id' => (string)$submitter->id,
                        'pivot' => [
                            'role_id' => Role::SUBMITTER_ROLE_ID,
                        ],
                    ],
                ],
            ],
        ];
        if ($case['allowed']) {
            array_push(
                $expected_query_response['submission']['users'],
                [
                    'id' => (string)$user_to_be_assigned->id,
                    'pivot' => [
                        'role_id' => $case['submission_user_role_id'],
                    ],
                ],
            );
        }
        $query_response->assertJsonPath('data', $expected_query_response);
    }

    /**
     * @return array
     */
    public function createSubmissionUserViaMutationAsAReviewCoordinatorProvider(): array
    {
        return [
            [
                [
                    'submission_user_role_id' => Role::SUBMITTER_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::REVIEWER_ROLE_ID,
                    'allowed' => true,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::REVIEW_COORDINATOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::EDITOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::PUBLICATION_ADMINISTRATOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::APPLICATION_ADMINISTRATOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => 0,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => '',
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => null,
                    'allowed' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider createSubmissionUserViaMutationAsAReviewCoordinatorProvider
     * @return void
     */
    public function testCreateSubmissionUserViaMutationAsAReviewCoordinator(array $case)
    {
        $publication = Publication::factory()->create();
        $submitter = User::factory()->create();
        /** @var User $review_coordinator */
        $review_coordinator = User::factory()->create();
        $this->actingAs($review_coordinator);
        $user_to_be_assigned = User::factory()->create();
        $submission = Submission::factory()
            ->for($publication)
            ->hasAttached($submitter, ['role_id' => Role::SUBMITTER_ROLE_ID])
            ->hasAttached($review_coordinator, ['role_id' => Role::REVIEW_COORDINATOR_ROLE_ID])
            ->create([
                'title' => 'Test Submission for Test User With Submission',
            ]);
        $mutation_response = $this->graphQL(
            'mutation CreateSubmissionUser ($role_id: ID!, $submission_id: ID!, $user_id: ID!) {
                createSubmissionUser(
                    submission_user: { role_id: $role_id, submission_id: $submission_id, user_id: $user_id }
                ) {
                    role_id
                    submission_id
                    user_id
                }
            }',
            [
                'role_id' => $case['submission_user_role_id'],
                'submission_id' => $submission->id,
                'user_id' => $user_to_be_assigned->id,
            ]
        );
        $expected_mutation_response = null;
        if ($case['allowed']) {
            $expected_mutation_response = [
                'createSubmissionUser' => [
                    'role_id' => $case['submission_user_role_id'],
                    'submission_id' => (string)$submission->id,
                    'user_id' => (string)$user_to_be_assigned->id,
                ],
            ];
        }
        $mutation_response->assertJsonPath('data', $expected_mutation_response);
        $query_response = $this->graphQL(
            'query GetSubmission ($id: ID!) {
                submission( id: $id ) {
                    users {
                        id
                        pivot {
                            role_id
                        }
                    }
                }
            }',
            [
                'id' => $submission->id,
            ]
        );
        $expected_query_response = [
            'submission' => [
                'users' => [
                    [
                        'id' => (string)$submitter->id,
                        'pivot' => [
                            'role_id' => Role::SUBMITTER_ROLE_ID,
                        ],
                    ],
                    [
                        'id' => (string)$review_coordinator->id,
                        'pivot' => [
                            'role_id' => Role::REVIEW_COORDINATOR_ROLE_ID,
                        ],
                    ],
                ],
            ],
        ];
        if ($case['allowed']) {
            array_push(
                $expected_query_response['submission']['users'],
                [
                    'id' => (string)$user_to_be_assigned->id,
                    'pivot' => [
                        'role_id' => $case['submission_user_role_id'],
                    ],
                ],
            );
        }
        $query_response_json = $query_response->decodeResponseJson();
        $query_response_value = AssertableJson::fromAssertableJsonString($query_response_json);
        $this->assertEquals($expected_query_response, $query_response_value->toArray()['data']);
    }

    /**
     * @return array
     */
    public function createSubmissionUserViaMutationAsAUserWithNoRoleProvider(): array
    {
        return [
            [
                [
                    'submission_user_role_id' => Role::SUBMITTER_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::REVIEWER_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::REVIEW_COORDINATOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::EDITOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::PUBLICATION_ADMINISTRATOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::APPLICATION_ADMINISTRATOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => 0,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => '',
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => null,
                    'allowed' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider createSubmissionUserViaMutationAsAUserWithNoRoleProvider
     * @return void
     */
    public function testCreateSubmissionUserViaMutationAsAUserWithNoRole(array $case)
    {
        $user_to_be_assigned = User::factory()->create();
        $publication = Publication::factory()->create();
        $submitter = User::factory()->create();
        $submission = Submission::factory()
            ->for($publication)
            ->hasAttached($submitter, ['role_id' => Role::SUBMITTER_ROLE_ID])
            ->create([
                'title' => 'Test Submission for Test User With Submission',
            ]);
        $mutation_response = $this->graphQL(
            'mutation CreateSubmissionUser ($role_id: ID!, $submission_id: ID!, $user_id: ID!) {
                createSubmissionUser(
                    submission_user: { role_id: $role_id, submission_id: $submission_id, user_id: $user_id }
                ) {
                    role_id
                    submission_id
                    user_id
                }
            }',
            [
                'role_id' => $case['submission_user_role_id'],
                'submission_id' => $submission->id,
                'user_id' => $user_to_be_assigned->id,
            ]
        );
        $mutation_response->assertJsonPath('data', null);
        $query_response = $this->graphQL(
            'query GetSubmission ($id: ID!) {
                submission( id: $id ) {
                    users {
                        id
                        pivot {
                            role_id
                        }
                    }
                }
            }',
            [
                'id' => $submission->id,
            ]
        );
        $expected_query_response = [
            'submission' => [
                'users' => [
                    [
                        'id' => (string)$submitter->id,
                        'pivot' => [
                            'role_id' => Role::SUBMITTER_ROLE_ID,
                        ],
                    ],
                ],
            ],
        ];
        $query_response->assertJsonPath('data', $expected_query_response);
    }

    /**
     * @return array
     */
    public function deleteSubmissionUserViaMutationAsAnApplicationAdministratorProvider(): array
    {
        return [
            [
                [
                    'submission_user_role_id' => Role::SUBMITTER_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::REVIEWER_ROLE_ID,
                    'allowed' => true,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::REVIEW_COORDINATOR_ROLE_ID,
                    'allowed' => true,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::EDITOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => 0,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => '',
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => null,
                    'allowed' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider deleteSubmissionUserViaMutationAsAnApplicationAdministratorProvider
     * @return void
     */
    public function testDeleteSubmissionUserViaMutationAsAnApplicationAdministrator(array $case)
    {
        /** @var User $administrator */
        $administrator = User::factory()->create();
        $administrator->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->actingAs($administrator);
        $publication = Publication::factory()->create();
        $user_to_be_deleted = User::factory()->create();
        $submission_user_role_id_is_invalid = intval($case['submission_user_role_id']) <= 0;
        $submission = Submission::factory()->hasAttached(
            $user_to_be_deleted,
            [
                'role_id' => $submission_user_role_id_is_invalid ? Role::SUBMITTER_ROLE_ID : $case['submission_user_role_id'],
            ]
        )
            ->for($publication)
            ->create([
                'title' => 'Test Submission for Reviewer Unassignment Via Mutation',
            ]);

        $submission_user = SubmissionUser::firstOrFail();
        $response = $this->graphQL(
            'mutation DeleteSubmissionUser ($role_id: ID!, $submission_id: ID!, $user_id: ID!) {
                deleteSubmissionUser(
                    role_id: $role_id, submission_id: $submission_id, user_id: $user_id
                ) {
                    id
                }
            }',
            [
                'role_id' => $case['submission_user_role_id'],
                'submission_id' => $submission->id,
                'user_id' => $user_to_be_deleted->id,
            ]
        );
        $expected_mutation_response = null;
        if ($case['allowed']) {
            $expected_mutation_response = [
                'deleteSubmissionUser' => [
                    'id' => (string)$submission_user->id,
                ],
            ];
        }
        $response->assertJsonPath('data', $expected_mutation_response);
    }

    /**
     * @return array
     */
    public function deleteSubmissionUserViaMutationAsAnEditorProvider(): array
    {
        return [
            [
                [
                    'submission_user_role_id' => Role::SUBMITTER_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::REVIEWER_ROLE_ID,
                    'allowed' => true,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::REVIEW_COORDINATOR_ROLE_ID,
                    'allowed' => true,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::EDITOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => 0,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => '',
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => null,
                    'allowed' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider deleteSubmissionUserViaMutationAsAnEditorProvider
     * @return void
     */
    public function testDeleteSubmissionUserViaMutationAsAnEditor(array $case)
    {
        /** @var User $editor */
        $editor = User::factory()->create();
        $editor->assignRole(Role::EDITOR);
        $this->actingAs($editor);
        $publication = Publication::factory()->create();
        $user_to_be_deleted = User::factory()->create();
        $submission_user_role_id_is_invalid = intval($case['submission_user_role_id']) <= 0;
        $submission = Submission::factory()->hasAttached(
            $user_to_be_deleted,
            [
                'role_id' => $submission_user_role_id_is_invalid ? Role::SUBMITTER_ROLE_ID : $case['submission_user_role_id'],
            ]
        )
            ->for($publication)
            ->create([
                'title' => 'Test Submission for Reviewer Unassignment Via Mutation',
            ]);

        $submission_user = SubmissionUser::firstOrFail();
        $response = $this->graphQL(
            'mutation DeleteSubmissionUser ($role_id: ID!, $submission_id: ID!, $user_id: ID!) {
                deleteSubmissionUser(
                    role_id: $role_id, submission_id: $submission_id, user_id: $user_id
                ) {
                    id
                }
            }',
            [
                'role_id' => $case['submission_user_role_id'],
                'submission_id' => $submission->id,
                'user_id' => $user_to_be_deleted->id,
            ]
        );
        $expected_mutation_response = null;
        if ($case['allowed']) {
            $expected_mutation_response = [
                'deleteSubmissionUser' => [
                    'id' => (string)$submission_user->id,
                ],
            ];
        }
        $response->assertJsonPath('data', $expected_mutation_response);
    }

    /**
     * @return array
     */
    public function deleteSubmissionUserViaMutationAsAReviewCoordinatorProvider(): array
    {
        return [
            [
                [
                    'submission_user_role_id' => Role::SUBMITTER_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::REVIEWER_ROLE_ID,
                    'allowed' => true,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::REVIEW_COORDINATOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::EDITOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => 0,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => '',
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => null,
                    'allowed' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider deleteSubmissionUserViaMutationAsAReviewCoordinatorProvider
     * @return void
     */
    public function testDeleteSubmissionUserViaMutationAsAReviewCoordinator(array $case)
    {
        /** @var User $review_coordinator */
        $review_coordinator = User::factory()->create();
        $this->actingAs($review_coordinator);
        $publication = Publication::factory()->create();
        $user_to_be_deleted = User::factory()->create();
        $submission_user_role_id_is_invalid = intval($case['submission_user_role_id']) <= 0;
        $submission = Submission::factory()->hasAttached(
            $user_to_be_deleted,
            [
                'role_id' => $submission_user_role_id_is_invalid ? Role::SUBMITTER_ROLE_ID : $case['submission_user_role_id'],
            ]
        )
            ->hasAttached($review_coordinator, ['role_id' => Role::REVIEW_COORDINATOR_ROLE_ID])
            ->for($publication)
            ->create([
                'title' => 'Test Submission for Reviewer Unassignment Via Mutation',
            ]);
        $submission_user = SubmissionUser::firstOrFail();
        $response = $this->graphQL(
            'mutation DeleteSubmissionUser ($role_id: ID!, $submission_id: ID!, $user_id: ID!) {
                deleteSubmissionUser(
                    role_id: $role_id, submission_id: $submission_id, user_id: $user_id
                ) {
                    id
                }
            }',
            [
                'role_id' => $case['submission_user_role_id'],
                'submission_id' => $submission->id,
                'user_id' => $user_to_be_deleted->id,
            ]
        );
        $expected_mutation_response = null;
        if ($case['allowed']) {
            $expected_mutation_response = [
                'deleteSubmissionUser' => [
                    'id' => (string)$submission_user->id,
                ],
            ];
        }
        $response->assertJsonPath('data', $expected_mutation_response);
    }

    /**
     * @return array
     */
    public function deleteSubmissionUserViaMutationAsAUserWithNoRoleProvider(): array
    {
        return [
            [
                [
                    'submission_user_role_id' => Role::SUBMITTER_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::REVIEWER_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::REVIEW_COORDINATOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => Role::EDITOR_ROLE_ID,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => 0,
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => '',
                    'allowed' => false,
                ],
            ],
            [
                [
                    'submission_user_role_id' => null,
                    'allowed' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider deleteSubmissionUserViaMutationAsAUserWithNoRoleProvider
     * @return void
     */
    public function testDeleteSubmissionUserViaMutationAsAUserWithNoRole(array $case)
    {
        $publication = Publication::factory()->create();
        $user_to_be_deleted = User::factory()->create();
        $submission_user_role_id_is_invalid = intval($case['submission_user_role_id']) <= 0;
        $submission = Submission::factory()->hasAttached(
            $user_to_be_deleted,
            [
                'role_id' => $submission_user_role_id_is_invalid ? Role::SUBMITTER_ROLE_ID : $case['submission_user_role_id'],
            ]
        )
            ->for($publication)
            ->create([
                'title' => 'Test Submission for Reviewer Unassignment Via Mutation',
            ]);
        $response = $this->graphQL(
            'mutation DeleteSubmissionUser ($role_id: ID!, $submission_id: ID!, $user_id: ID!) {
                deleteSubmissionUser(
                    role_id: $role_id, submission_id: $submission_id, user_id: $user_id
                ) {
                    id
                }
            }',
            [
                'role_id' => $case['submission_user_role_id'],
                'submission_id' => $submission->id,
                'user_id' => $user_to_be_deleted->id,
            ]
        );
        $expected_mutation_response = null;
        $response->assertJsonPath('data', $expected_mutation_response);
    }

    /**
     * @return void
     */
    public function testSubmissionStatusCanBeRetrievedAndChangedViaEloquent()
    {
        $submission = Submission::factory()->create();
        $this->assertEquals(Submission::INITIALLY_SUBMITTED, $submission->status);
        $this->assertEquals("INITIALLY_SUBMITTED", $submission->status_name);
        $submission->status = Submission::AWAITING_REVIEW;
        $this->assertEquals(Submission::AWAITING_REVIEW, $submission->status);
        $this->assertEquals("AWAITING_REVIEW", $submission->status_name);
    }
}
