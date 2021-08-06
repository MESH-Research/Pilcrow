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
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    private const SUBMITTER_ROLE_ID = 6;

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
                'Test Submission #7',
                [
                    'createSubmission' => [
                        'title' => 'Test Submission #7',
                        'publication' => [
                            'name' => 'Test Publication #6',
                        ],
                    ],
                ],
            ],
            [
                '        Test Submission #8 with Whitespace       ',
                [
                    'createSubmission' => [
                        'title' => 'Test Submission #8 with Whitespace',
                        'publication' => [
                            'name' => 'Test Publication #6',
                        ],
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
     * @dataProvider createSubmissionMutationProvider
     * @return void
     */
    public function testSubmissionCreationViaMutation(mixed $title, mixed $expected_data)
    {
        $publication = Publication::factory()->create([
            'name' => 'Test Publication #6',
        ]);
        $response = $this->graphQL(
            'mutation CreateSubmission ($title: String!, $publication_id: ID!) {
                createSubmission(
                    input: { title: $title, publication_id: $publication_id }
                ) {
                    title
                    publication {
                        name
                    }
                }
            }',
            [
                'title' => $title,
                'publication_id' => $publication->id,
            ]
        );
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testSubmissionUserCreationViaMutation()
    {
        $publication = Publication::factory()->create();
        $user = User::factory()->create();
        $submission = Submission::factory()
            ->for($publication)
            ->create([
                'title' => 'Test Submission #9 for Test User With Submission',
            ]);
        $response = $this->graphQL(
            'mutation CreateSubmissionUser ($role_id: ID!, $submission_id: ID!, $user_id: ID!) {
                createSubmissionUser(
                    input: { role_id: $role_id, submission_id: $submission_id, user_id: $user_id }
                ) {
                    role_id
                    submission_id
                    user_id
                }
            }',
            [
                'role_id' => self::SUBMITTER_ROLE_ID,
                'submission_id' => $submission->id,
                'user_id' => $user->id,
            ]
        );
        $expected_data = [
            'createSubmissionUser' => [
                'role_id' => (string)self::SUBMITTER_ROLE_ID,
                'submission_id' => (string)$submission->id,
                'user_id' => (string)$user->id,
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
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
        $expected_data = [
            'submission' => [
                'users' => [
                    [
                        'id' => (string)$user->id,
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
                'publication_id' => $submission->id,
            ]
        )
            ->get();
        $this->assertEquals(1, $submission_pivot_data->count());
    }
}
