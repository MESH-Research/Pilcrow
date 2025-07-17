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

class SubmissionScopesTest extends ApiTestCase
{

    use RefreshDatabase;

    private User $user;

    private Publication $publication;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()
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
            ]
        ];
        $SubmissionFactory = Submission::factory();

        foreach ($records as $record) {
            $f = $SubmissionFactory
                ->state([
                    'status' => $record['status'],
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

        //Create a publication that our user is assigned to.
        [$this->publication] = Publication::factory()
            ->hasAttached($this->user, [], 'editors')
            ->count(1)
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

    public static function myRoleScopeDataProvider()
    {
        return [
            'submitter' => [
                'my_roles' => ['submitter'],
                'expected_count' => 1,
            ],
            'reviewer' => [
                'my_roles' => ['reviewer'],
                'expected_count' => 1,
            ],
            'review_coordinator' => [
                'my_roles' => ['review_coordinator'],
                'expected_count' => 5,
            ],
            'reviewer+rc' => [
                'my_roles' => ['reviewer', 'review_coordinator'],
                'expected_count' => 6,
            ]
        ];
    }


    #[DataProvider('myRoleScopeDataProvider')]
    public function testMyRoleScope(array $my_roles, int $expected_count)
    {

        $this->actingAs($this->user);

        $response = $this->graphQL(
            /** @lang GraphQL */
            'query TestMyRoleScope($my_roles: [SubmissionUserRoles!]) {
        submissions(page: 1, first: 100, my_roles: $my_roles) {
                data {
                    id
                    my_role
                }
                paginatorInfo {
                    count
                }
            }
        }
        ',
            [
                'my_roles' => $my_roles,
            ]
        );

        $response->assertStatus(200);
        $response->assertJsonPath('data.submissions.paginatorInfo.count', $expected_count);

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has(
                'data',
                fn(AssertableJson $json) =>
                $json
                    ->has(
                        'submissions.data',
                        $expected_count,
                        fn(AssertableJson $json) =>
                        $json
                            ->where('my_role', fn($role) => in_array($role, $my_roles))
                            ->etc()
                    )
                    ->has('submissions.paginatorInfo')
            )
        );
    }

    public static function statusScopeDataProvider()
    {
        return [
            'draft' => [
                'statuses' => ['DRAFT'],
                'expected_count' => 2,
            ],
            'initially_submitted' => [
                'statuses' => ['INITIALLY_SUBMITTED'],
                'expected_count' => 3,
            ],
            'under_review' => [
                'statuses' => ['UNDER_REVIEW'],
                'expected_count' => 5,
            ],
            'draft+initially_submitted+under_review' => [
                'statuses' => [
                    'DRAFT',
                    'INITIALLY_SUBMITTED',
                    'UNDER_REVIEW'
                ],
                'expected_count' => 10,
            ]
        ];
    }

    #[DataProvider("statusScopeDataProvider")]
    public function testStatusScope(array $statuses, int $expected_count): void
    {
        $this->actingAs($this->user);

        $response = $this->graphQL(
            /** @lang GraphQL */
            'query TestStatusScope($status: [SubmissionStatus!]) {
        submissions(page: 1, first: 100, status: $status) {
                data {
                    id
                    status
                    my_role
                }
                paginatorInfo {
                    count
                }
            }
        }
        ',
            [
                'status' => $statuses,
            ]
        );

        $response->assertStatus(200);
        $response->assertJsonPath('data.submissions.paginatorInfo.count', $expected_count);

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has(
                'data',
                fn(AssertableJson $json) =>
                $json
                    ->has(
                        'submissions.data',
                        $expected_count,
                        fn(AssertableJson $json) =>
                        $json
                            ->where('status', fn($status) => in_array($status, $statuses))
                            ->etc()
                    )
                    ->has('submissions.paginatorInfo')
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
        $this->actingAs($this->user);

        $response = $this->graphQL(
            /** @lang GraphQL */
            'query TestStatusAndMyRoleScope($status: [SubmissionStatus!], $my_roles: [SubmissionUserRoles!]) {
        submissions(page: 1, first: 100, status: $status, my_roles: $my_roles) {
                data {
                    id
                    status
                    my_role
                }
                paginatorInfo {
                    count
                }
            }
        }
        ',
            [
                'status' => $statuses,
                'my_roles' => $roles,
            ]
        );

        $response->assertStatus(200);
        $response->assertJsonPath('data.submissions.paginatorInfo.count', $expectedCount);

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data', function (AssertableJson $json) use ($roles, $statuses, $expectedCount) {
                if ($expectedCount === 0) {
                    $json->has('submissions.data', 0);
                    return;
                }
                $json
                    ->has(
                        'submissions.data',
                        $expectedCount,
                        fn(AssertableJson $json) =>
                        $json
                            ->where('status', fn($status) => in_array($status, $statuses))
                            ->where('my_role', fn($role) => in_array($role, $roles))
                            ->etc()
                    )
                    ->has('submissions.paginatorInfo');
            })
        );
    }

    public static function publicationScopeDataProvider(): array
    {
        return [
            'publication all status' => [
                'statuses' => ['DRAFT', 'INITIALLY_SUBMITTED', 'UNDER_REVIEW', 'ACCEPTED_AS_FINAL'],
                'expectedCount' => 5,
            ],
            'publication + draft' => [
                'statuses' => ['DRAFT'],
                'expectedCount' => 1,
            ],
        ];
    }

    #[DataProvider("publicationScopeDataProvider")]
    public function testPublicationScopeAndStatus(?array $statuses, int $expectedCount): void
    {
        $this->actingAs($this->user);

        $variables = [
            'publicationId' => $this->publication->id,
        ];

        if (!empty($statuses)) {
            $variables['statuses'] = $statuses;
        }

        $response = $this->graphQL(
            /** @lang GraphQL */
            'query TestPublicationScope($statuses: [SubmissionStatus!], $publicationId: ID!) {
                submissions(page: 1, first: 100, publication: [$publicationId], status: $statuses) {
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
            ',
            $variables
        );

        $response->assertStatus(200);
        $response->assertJsonPath('data.submissions.paginatorInfo.count', $expectedCount);

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has(
                'data',
                fn(AssertableJson $json) =>

                $json
                    ->has('submissions.data', $expectedCount)
                    ->has(
                        'submissions.data',
                        $expectedCount,
                        fn(AssertableJson $json) =>
                        $json
                            ->where('status', fn($status) => in_array($status, $statuses))
                            ->where('publication.id', (string)$this->publication->id)
                            ->etc()
                    )
                    ->has('submissions.paginatorInfo')
            )
        );
    }

    public function testPublicationScope(): void
    {
        $this->actingAs($this->user);

        $variables = [
            'publicationId' => $this->publication->id,
        ];

        $response = $this->graphQL(
            /** @lang GraphQL */
            'query TestPublicationScope($publicationId: ID!) {
                submissions(page: 1, first: 100, publication: [$publicationId]) {
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
            ',
            $variables
        );

        $response->assertStatus(200);
        $response->assertJsonPath('data.submissions.paginatorInfo.count', 5);

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has(
                'data',
                fn(AssertableJson $json) =>
                $json
                    ->has(
                        'submissions.data',
                        5,
                        fn(AssertableJson $json) =>
                        $json
                            ->where('publication.id', (string)$this->publication->id)
                            ->etc()
                    )
                    ->has('submissions.paginatorInfo')
            )
        );
    }
}
