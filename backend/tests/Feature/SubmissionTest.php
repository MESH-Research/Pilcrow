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
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    // TODO: Refactor this out of this class
    private const SUBMITTER_ROLE_ID = 6;
    private const REVIEWER_ROLE_ID = 5;
    private const REVIEW_COORDINATOR_ROLE_ID = 4;
    private const EDITOR_ROLE_ID = 3;
    private const PUBLICATION_ADMINISTRATOR_ROLE_ID = 2;
    private const APPLICATION_ADMINISTRATOR_ROLE_ID = 1;

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
        $submission_count = 4;
        $user_count = 6;
        $publication = Publication::factory()->create([
            'name' => 'Test Publication #2',
        ]);
        $users = User::factory()->count($user_count)->create();

        // Create submissions and attach them to users randomly with random roles
        for ($i = 0; $i < $submission_count; $i++) {
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
                $users->random(),
                [
                    'role_id' => $random_role_id,
                ]
            )
                ->for($publication)
                ->create();

            // Ensure at least one Submitter is attached if one was not previously attached
            if ($random_role_id !== self::SUBMITTER_ROLE_ID) {
                $submission->users()->attach(
                    $users->random(),
                    [
                        'role_id' => self::SUBMITTER_ROLE_ID,
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
                'role_id' => self::SUBMITTER_ROLE_ID,
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
                'role_id' => self::SUBMITTER_ROLE_ID,
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
                            'role_id' => (string)self::SUBMITTER_ROLE_ID,
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
                'role_id' => self::SUBMITTER_ROLE_ID,
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
                            'role_id' => (string)self::SUBMITTER_ROLE_ID,
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
                            users: { connect: [{ id: $submitter_user_id, role_id: 6 }] }
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
     * @param string $role
     * @return array
     */
    private function createExpectedData1Array($role)
    {
        return [
            'createSubmissionUser' => [
                'role_id' => (string)$role,
                'submission_id' => null,
                'user_id' => null,
            ],
        ];
    }

    /**
     * @param string|null $role
     * @return array
     */
    private function createExpectedData2Array($role)
    {
        $inner_data = $role !== null
            ? [
                [
                    'id' => null,
                    'pivot' => [
                        'role_id' => (string)$role,
                    ],
                ],
            ]
            : [];

        return [
            'submission' => [
                'users' => $inner_data,
            ],
        ];
    }

    /**
     * TODO: When functionality is developed to create submission users other than reviewers,
     * change the expected data of each of these test cases.
     *
     * @return array
     */
    public function createSubmissionUserViaMutationProvider(): array
    {
        return [
            [
                [
                    'acting_as_role' => Role::APPLICATION_ADMINISTRATOR,
                    'submission_user_role_id' => self::SUBMITTER_ROLE_ID,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => Role::APPLICATION_ADMINISTRATOR,
                    'submission_user_role_id' => self::REVIEWER_ROLE_ID,
                    'expected_data_1' => $this->createExpectedData1Array(self::REVIEWER_ROLE_ID),
                    'expected_data_2' => $this->createExpectedData2Array(self::REVIEWER_ROLE_ID),
                ],
            ],
            [
                [
                    'acting_as_role' => Role::APPLICATION_ADMINISTRATOR,
                    'submission_user_role_id' => self::REVIEW_COORDINATOR_ROLE_ID,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => Role::APPLICATION_ADMINISTRATOR,
                    'submission_user_role_id' => self::EDITOR_ROLE_ID ,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => Role::APPLICATION_ADMINISTRATOR,
                    'submission_user_role_id' => self::PUBLICATION_ADMINISTRATOR_ROLE_ID ,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => Role::APPLICATION_ADMINISTRATOR,
                    'submission_user_role_id' => self::APPLICATION_ADMINISTRATOR_ROLE_ID,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => Role::APPLICATION_ADMINISTRATOR,
                    'submission_user_role_id' => null,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => Role::APPLICATION_ADMINISTRATOR,
                    'submission_user_role_id' => '',
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => Role::APPLICATION_ADMINISTRATOR,
                    'submission_user_role_id' => 0,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => Role::REVIEW_COORDINATOR,
                    'submission_user_role_id' => self::SUBMITTER_ROLE_ID,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => Role::REVIEW_COORDINATOR,
                    'submission_user_role_id' => self::REVIEWER_ROLE_ID,
                    'expected_data_1' => $this->createExpectedData1Array(self::REVIEWER_ROLE_ID),
                    'expected_data_2' => $this->createExpectedData2Array(self::REVIEWER_ROLE_ID),
                ],
            ],
            [
                [
                    'acting_as_role' => Role::REVIEW_COORDINATOR,
                    'submission_user_role_id' => self::REVIEW_COORDINATOR_ROLE_ID,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => Role::REVIEW_COORDINATOR,
                    'submission_user_role_id' => self::EDITOR_ROLE_ID ,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => Role::REVIEW_COORDINATOR,
                    'submission_user_role_id' => self::PUBLICATION_ADMINISTRATOR_ROLE_ID ,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => Role::REVIEW_COORDINATOR,
                    'submission_user_role_id' => self::APPLICATION_ADMINISTRATOR_ROLE_ID,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => Role::REVIEW_COORDINATOR,
                    'submission_user_role_id' => null,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => Role::REVIEW_COORDINATOR,
                    'submission_user_role_id' => '',
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => Role::REVIEW_COORDINATOR,
                    'submission_user_role_id' => 0,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => false,
                    'submission_user_role_id' => self::SUBMITTER_ROLE_ID,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => false,
                    'submission_user_role_id' => self::REVIEWER_ROLE_ID,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => false,
                    'submission_user_role_id' => self::REVIEW_COORDINATOR_ROLE_ID,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => false,
                    'submission_user_role_id' => self::SUBMITTER_ROLE_ID,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => false,
                    'submission_user_role_id' => self::REVIEWER_ROLE_ID,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => false,
                    'submission_user_role_id' => self::REVIEW_COORDINATOR_ROLE_ID,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => false,
                    'submission_user_role_id' => self::EDITOR_ROLE_ID ,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => false,
                    'submission_user_role_id' => self::PUBLICATION_ADMINISTRATOR_ROLE_ID ,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => false,
                    'submission_user_role_id' => self::APPLICATION_ADMINISTRATOR_ROLE_ID,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => false,
                    'submission_user_role_id' => null,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => false,
                    'submission_user_role_id' => '',
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
            [
                [
                    'acting_as_role' => false,
                    'submission_user_role_id' => 0,
                    'expected_data_1' => null,
                    'expected_data_2' => $this->createExpectedData2Array(null),
                ],
            ],
        ];
    }

    /**
     * @dataProvider createSubmissionUserViaMutationProvider
     * @return void
     */
    public function testCreateSubmissionUserViaMutation(array $case)
    {
        if ($case['acting_as_role'] === Role::APPLICATION_ADMINISTRATOR) {
            $acting_as = User::factory()->create();
            $acting_as->assignRole($case['acting_as_role']);
            $this->actingAs($acting_as);
        }
        $publication = Publication::factory()->create();
        $user = User::factory()->create();
        $submission = Submission::factory()
            ->for($publication)
            ->create([
                'title' => 'Test Submission for Test User With Submission',
            ]);
        if ($case['acting_as_role'] === Role::REVIEW_COORDINATOR) {
            $review_coordinator = User::factory()->create();
            $submission = Submission::factory()
                ->for($publication)
                ->hasAttached($review_coordinator, ['role_id' => 4])
                ->create([
                    'title' => 'Test Submission for Test User With Submission',
            ]);
        } else {
            $submission = Submission::factory()
                ->for($publication)
                ->create([
                    'title' => 'Test Submission for Test User With Submission',
            ]);
        }
        $response = $this->graphQL(
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
                'user_id' => $user->id,
            ]
        );
        if ($case['expected_data_1'] !== null) {
            $case['expected_data_1']['createSubmissionUser']['user_id'] = (string)$user->id;
            $case['expected_data_1']['createSubmissionUser']['submission_id'] = (string)$submission->id;
        }
        $response->assertJsonPath('data', $case['expected_data_1']);
        $response = $this->graphQL(
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
        if ($case['expected_data_1'] !== null) {
            $case['expected_data_2']['submission']['users'][0]['id'] = (string)$user->id;
            if ($case['acting_as_role'] === Role::REVIEW_COORDINATOR) {
                print_r(' amending ');
                array_push(
                    $case['expected_data_2']['submission']['users'],
                    [
                        'id' => (string)$review_coordinator->id,
                        'pivot' => [
                            'role_id' => "4",
                        ]
                    ]
                );
            }
        }
        $response->assertJsonPath('data', $case['expected_data_2']);
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
    public function deleteSubmissionUserMutationProvider(): array
    {
        return [
            [
                self::REVIEWER_ROLE_ID,
            ],
        ];
    }

    /**
     * @dataProvider deleteSubmissionUserMutationProvider
     * @return void
     */
    public function testSubmissionUserDeletionViaMutation(int $role_id)
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->actingAs($admin);
        $publication = Publication::factory()->create();
        $user = User::factory()->create();
        $submission = Submission::factory()->hasAttached(
            $user,
            [
                'role_id' => $role_id,
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
                'role_id' => $role_id,
                'submission_id' => $submission->id,
                'user_id' => $user->id,
            ]
        );
        $expected_data = [
            'deleteSubmissionUser' => [
                'id' => (string)$submission->id,
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }
}
