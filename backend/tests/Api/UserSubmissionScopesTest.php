<?php

namespace Tests\Api;

use App\Models\Publication;
use App\Models\Role;
use App\Models\Submission;
use App\Models\SubmissionAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\ApiTestCase;

class UserSubmissionScopesTest extends ApiTestCase
{

    use RefreshDatabase;

    private User $user;

    private Publication $publication;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()
            ->create();

        //Create a publication that our user is assigned to.
        [$this->publication] = Publication::factory()
            ->hasAttached($this->user, [], 'editors')
            ->count(1)
            ->create();

        $records = [
            [
                'status' => Submission::DRAFT,
                'role' => Role::SUBMITTER_ROLE_ID,
            ],
            [
                'status' => Submission::INITIALLY_SUBMITTED,
                'role' => Role::REVIEWER_ROLE_ID,
            ],
            [
                'status' => Submission::INITIALLY_SUBMITTED,
                'role' => Role::REVIEW_COORDINATOR_ROLE_ID,
            ],
            [
                'status' => Submission::UNDER_REVIEW,
                'role' => Role::REVIEW_COORDINATOR_ROLE_ID,
            ],
            [
                'status' => Submission::ACCEPTED_AS_FINAL,
                'role' => Role::REVIEW_COORDINATOR_ROLE_ID,
            ],
            [
                'status' => Submission::UNDER_REVIEW,
                'role' => Role::REVIEW_COORDINATOR_ROLE_ID,
            ],
            [
                'status' => Submission::UNDER_REVIEW,
                'role' => Role::REVIEW_COORDINATOR_ROLE_ID,
                'publication' => $this->publication
            ]
        ];
        $SubmissionFactory = Submission::factory();

        foreach ($records as $record) {
            $f = $SubmissionFactory
                ->state([
                    'status' => $record['status'],
                    'publication_id' => !empty($record['publication']) ? $record['publication'] : Publication::factory(),
                ])
                ->has(SubmissionAssignment::factory()->state([
                    'role_id' => $record['role'],
                    'user_id' => $this->user->id,
                ]));
            $otherRoles = array_filter([4, 5, 6], function ($role) use ($record) {
                return $role !== $record['role'];
            });
            foreach ($otherRoles as $otherRole) {
                $f->has(SubmissionAssignment::factory()->state([
                    'role_id' => $otherRole,
                    'user_id' => User::factory(),
                ]));
            }
            $f->create();
        }

        //Add some additional submissions that shouldn't be included in our searches
        $SubmissionFactory
            ->count(5)
            ->create();





        //Create some submissions in that publication that we're not assigned to.
        $SubmissionFactory
            ->for($this->publication)
            ->state(new Sequence(
                ['status' => Submission::DRAFT],
                ['status' => Submission::INITIALLY_SUBMITTED],
                ['status' => Submission::UNDER_REVIEW],
                ['status' => Submission::ACCEPTED_AS_FINAL],
                ['status' => Submission::UNDER_REVIEW],
            ))
            ->hasReviewers(2)
            ->hasReviewCoordinators(1)
            ->hasSubmitters(2)
            ->count(5)
            ->create();
    }

    public static function roleScopeDataProvider()
    {
        return [
            'submitter' => [
                'roles' => ['submitter'],
                'expected_count' => 1,
            ],
            'reviewer' => [
                'roles' => ['reviewer'],
                'expected_count' => 1,
            ],
            'review_coordinator' => [
                'roles' => ['review_coordinator'],
                'expected_count' => 5,
            ],
            'reviewer+rc' => [
                'roles' => ['reviewer', 'review_coordinator'],
                'expected_count' => 6,
            ]
        ];
    }


    #[DataProvider('roleScopeDataProvider')]
    public function testRoleScope(array $roles, int $expected_count)
    {


        $response = $this->graphQL(
            /** @lang GraphQL */
            'query TestMyRoleScope($userId: ID! $roles: [SubmissionUserRoles!]) {
                user(id: $userId) {
                    assigned_submissions(page: 1, first: 100, roles: $roles) {
                        data {
                            assignments {
                                role
                                user {
                                    id
                                }
                            }
                        }
                        paginatorInfo {
                            count
                        }
                    }
                }
            }
        ',
            [
                'userId' => $this->user->id,
                'roles' => $roles,
            ]
        );

        $response->assertStatus(200);
        $response->assertJsonPath('data.user.assigned_submissions.paginatorInfo.count', $expected_count);

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has(
                'data',
                fn(AssertableJson $json) =>
                $json
                    ->has(
                        'user.assigned_submissions.data',
                        $expected_count,
                        fn(AssertableJson $json) =>
                        $json
                            ->has(
                                'assignments',
                                1,
                                fn(AssertableJson $json) =>
                                $json->where('role', fn($role) => in_array($role, $roles))
                                    ->where('user.id', fn($userId) => $userId === (string)$this->user->id)
                                    ->etc()
                            )
                    )
                    ->has('user.assigned_submissions.paginatorInfo')
            )

        );
    }

    public static function statusScopeDataProvider()
    {
        return [
            'draft' => [
                'statuses' => ['DRAFT'],
                'expected_count' => 1,
            ],
            'initially_submitted' => [
                'statuses' => ['INITIALLY_SUBMITTED'],
                'expected_count' => 2,
            ],
            'under_review' => [
                'statuses' => ['UNDER_REVIEW'],
                'expected_count' => 3,
            ],
            'draft+initially_submitted+under_review' => [
                'statuses' => [
                    'DRAFT',
                    'INITIALLY_SUBMITTED',
                    'UNDER_REVIEW'
                ],
                'expected_count' => 6,
            ]
        ];
    }

    #[DataProvider("statusScopeDataProvider")]
    public function testStatusScope(array $statuses, int $expected_count): void
    {
        $this->beAppAdmin();

        $response = $this->graphQL(
            /** @lang GraphQL */
            'query TestStatusScope($userId: ID!, $status: [SubmissionStatus!]) {
                user(id: $userId) {
        assigned_submissions(page: 1, first: 100, status: $status) {
                data {
                    assignments {
                        user {
                            id
                        }
                    }
                    status
                }
                paginatorInfo {
                    count
                }
            }
        }
        }
        ',
            [
                'userId' => $this->user->id,
                'status' => $statuses,
            ]
        );

        $response->assertStatus(200);
        $response->assertJsonPath('data.user.assigned_submissions.paginatorInfo.count', $expected_count);

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has(
                'data',
                fn(AssertableJson $json) =>
                $json
                    ->has(
                        'user.assigned_submissions.data',
                        $expected_count,
                        fn(AssertableJson $json) =>
                        $json
                            ->where('status', fn($status) => in_array($status, $statuses))
                            ->has(
                                'assignments',
                                1,
                                fn(AssertableJson $json) =>
                                $json
                                    ->where('user.id', fn($userId) => $userId === (string)$this->user->id)
                                    ->etc()
                            )
                            ->etc()
                    )
                    ->has('user.assigned_submissions.paginatorInfo')
            )
        );
    }

    public static function statusAndRolesDataProvider()
    {
        return [
            'draft & submitter' => [
                'statuses' => ['DRAFT'],
                'roles' => ['submitter'],
                'expectedCount' => 1,
            ],
            'initially_submitted & submitter' => [
                'statuses' => ['INITIALLY_SUBMITTED'],
                'roles' => ['submitter'],
                'expectedCount' => 0,
            ],
            'initially_submitted & reviewer' => [
                'statuses' => ['INITIALLY_SUBMITTED'],
                'roles' => ['reviewer'],
                'expectedCount' => 1,
            ],
            'initially_submitted & reviewer+rc' => [
                'statuses' => ['INITIALLY_SUBMITTED'],
                'roles' => ['reviewer', 'review_coordinator'],
                'expectedCount' => 2,
            ],
            'initially_submitted & review_coordinator' => [
                'statuses' => ['INITIALLY_SUBMITTED'],
                'roles' => ['review_coordinator'],
                'expectedCount' => 1,
            ],
        ];
    }

    #[DataProvider("statusAndRolesDataProvider")]
    public function testStatusAndMyRoleScope(array $statuses, array $roles, int $expectedCount)
    {
        $this->beAppAdmin();

        $response = $this->graphQL(
            /** @lang GraphQL */
            'query TestStatusAndMyRoleScope($status: [SubmissionStatus!], $roles: [SubmissionUserRoles!], $userId: ID!) {
                user(id: $userId) {
        assigned_submissions(page: 1, first: 100, status: $status, roles: $roles) {

                data {
                    status
                    assignments {
                        role
                        user {
                            id
                        }
                    }
                }
                paginatorInfo {
                    count
                }
            }
        }
        }
        ',
            [
                'userId' => $this->user->id,
                'status' => $statuses,
                'roles' => $roles,
            ]
        );

        $response->assertStatus(200);
        $response->assertJsonPath('data.user.assigned_submissions.paginatorInfo.count', $expectedCount);

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data', function (AssertableJson $json) use ($roles, $statuses, $expectedCount) {
                if ($expectedCount === 0) {
                    $json->has('user.assigned_submissions.data', 0);
                    return;
                }
                $json
                    ->has(
                        'user.assigned_submissions.data',
                        $expectedCount,
                        fn(AssertableJson $json) =>
                        $json
                            ->where('status', fn($status) => in_array($status, $statuses))
                            ->has(
                                'assignments',
                                1,
                                fn(AssertableJson $json) =>
                                $json->where('role', fn($role) => in_array($role, $roles))
                                    ->where('user.id', fn($userId) => $userId === (string)$this->user->id)
                                    ->etc()
                            )
                            ->etc()
                    )
                    ->has('user.assigned_submissions.paginatorInfo');
            })
        );
    }

    public static function publicationScopeDataProvider(): array
    {
        return [
            'publication all status' => [
                'statuses' => ['DRAFT', 'INITIALLY_SUBMITTED', 'UNDER_REVIEW', 'ACCEPTED_AS_FINAL'],
                'expectedCount' => 1,
            ],
            'publication + draft' => [
                'statuses' => ['DRAFT'],
                'expectedCount' => 0,
            ],
        ];
    }

    #[DataProvider("publicationScopeDataProvider")]
    public function testPublicationScopeAndStatus(?array $statuses, int $expectedCount): void
    {
        $this->beAppAdmin();

        $variables = [
            'userId' => $this->user->id,
            'publicationId' => $this->publication->id,
            'statuses' => $statuses
        ];

        $response = $this->graphQL(
            /** @lang GraphQL */
            'query TestPublicationScope($statuses: [SubmissionStatus!], $publicationId: ID!, $userId: ID!) {
                user(id: $userId) {
                assigned_submissions(page: 1, first: 100, publication: [$publicationId], status: $statuses) {
                    paginatorInfo {
                        count
                    }
                    data {
                        status
                        publication {
                            id
                        }
                    }
                }
            }
            }
            ',
            $variables
        );

        $response->assertStatus(200);
        $response->assertJsonPath('data.user.assigned_submissions.paginatorInfo.count', $expectedCount);

        if ($expectedCount === 0) {
            $response->assertJsonPath('data.user.assigned_submissions.data', []);
            return;
        }

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has(
                'data',
                fn(AssertableJson $json) =>

                $json
                    ->has('user.assigned_submissions.data', $expectedCount)
                    ->has(
                        'user.assigned_submissions.data',
                        $expectedCount,
                        fn(AssertableJson $json) =>
                        $json
                            ->where('status', fn($status) => in_array($status, $statuses))
                            ->where('publication.id', (string)$this->publication->id)
                            ->etc()
                    )
                    ->has('user.assigned_submissions.paginatorInfo')
            )
        );
    }

    public function testPublicationScope(): void
    {
        $this->beAppAdmin();

        $variables = [
            'userId' => $this->user->id,
            'publicationId' => $this->publication->id,
        ];

        $response = $this->graphQL(
            /** @lang GraphQL */
            'query TestPublicationScope($publicationId: ID!, $userId: ID!) {
                user(id: $userId) {
                assigned_submissions(page: 1, first: 100, publication: [$publicationId]) {
                    paginatorInfo {
                        count
                    }
                    data {
                        status
                        publication {
                            id
                        }
                    }
                }
            }
            }
            ',
            $variables
        );

        $response->assertStatus(200);
        $response->assertJsonPath('data.user.assigned_submissions.paginatorInfo.count', 1);

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has(
                'data',
                fn(AssertableJson $json) =>
                $json
                    ->has(
                        'user.assigned_submissions.data',
                        1,
                        fn(AssertableJson $json) =>
                        $json
                            ->where('publication.id', (string)$this->publication->id)
                            ->etc()
                    )
                    ->has('user.assigned_submissions.paginatorInfo')
            )
        );
    }
}
