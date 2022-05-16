<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
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
        $this->beAppAdmin();

        $publication = Publication::factory()->create([
            'name' => 'Test Publication #2',
        ]);

        [$submitter, $reviewer, $reviewCoordinator] = User::factory()->count(3)->create();

        $submission = Submission::factory()
            ->hasAttached($submitter, [], 'submitters')
            ->hasAttached($reviewer, [], 'reviewers')
            ->hasAttached($reviewCoordinator, [], 'reviewCoordinators')
            ->for($publication)
            ->create();

        $response = $this->graphQL(
            'query GetSubmission($id: ID!) {
                submission(id: $id) {
                    id
                    title
                    reviewers {
                        id
                    }
                    review_coordinators {
                        id
                    }
                    submitters {
                        id
                    }
                }
            }',
            [ 'id' => $submission->id ]
        );

        $response->assertJson(fn (AssertableJson $json) =>
            $json
                ->has('data', fn ($json) =>
                    $json->has('submission', fn ($json) =>
                        $json->has('reviewers', 1, fn ($json) =>
                            $json->where('id', (string)$reviewer->id))
                        ->has('submitters', 1, fn ($json) =>
                            $json->where('id', (string)$submitter->id))
                        ->has('review_coordinators', 1, fn ($json) =>
                            $json->where('id', (string)$reviewCoordinator->id))
                        ->etc())));
    }

    /**
     * @dataProvider allSubmissionRoles
     * @return void
     */
    public function testAllSubmissionRolesCanViewSubmission($role)
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $submission = Submission::factory()
            ->hasAttached($user, [], Str::camel($role))
            ->create([
                'title' => 'Test Submission',
            ]);
        $response = $this->graphQL(
            'query GetSubmission($id: ID!) {
                submission (id: $id) {
                    id
                }
            }',
            [ 'id' => $submission->id ]
        );

        $response->assertJsonPath('data.submission.id', (string)$submission->id);
    }

    public function testPublicationAdminCanViewSubmission()
    {
        /** @var User $publicationAdmin */
        $publicationAdmin = User::factory()->create();
        $this->actingAs($publicationAdmin);

        $publication = Publication::factory()
            ->hasAttached($publicationAdmin, ['role_id' => Role::PUBLICATION_ADMINISTRATOR_ROLE_ID])
            ->create();

        $submission = Submission::factory()
            ->for($publication)
            ->create([
                'title' => 'Test Submission',
            ]);

        $response = $this->graphQL(
            'query GetSubmission($id: ID!) {
                submission (id: $id) {
                    id
                }
            }',
            [ 'id' => $submission->id ]
        );

        $response->assertJsonPath('data.submission.id', (string)$submission->id);
    }

    public function testOtherUsersCannotViewSubmission()
    {
         /** @var User $user */
         $user = User::factory()->create();
         $this->actingAs($user);

         $submission = Submission::factory()
             ->create([
                 'title' => 'Test Submission',
             ]);
         $response = $this->graphQL(
             'query GetSubmission($id: ID!) {
                 submission (id: $id) {
                     id
                 }
             }',
             [ 'id' => $submission->id ]
         );

         $response->assertJsonPath('errors.0.extensions.category', 'authorization');
    }

    public function testGuestsCannotViewSubmission()
    {
         $submission = Submission::factory()
             ->create([
                 'title' => 'Test Submission',
             ]);
         $response = $this->graphQL(
             'query GetSubmission($id: ID!) {
                 submission (id: $id) {
                     id
                 }
             }',
             [ 'id' => $submission->id ]
         );

         $response->assertJsonPath('errors.0.extensions.category', 'authorization');
    }

    /**
     * @return void
     */
    public function testSubmissionsCanBeQueriedForAPublication()
    {
        $publication = Publication::factory()->create([
            'name' => 'Test Publication #3',
        ]);

        $submission = Submission::factory()
            ->hasAttached(User::factory()->create(), [], 'submitters')
            ->for($publication)
            ->create([
                'title' => 'Test Submission',
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
                        'title' => 'Test Submission',
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
        $submission = Submission::factory()
            ->hasAttached($user, [], 'submitters')
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
    public function testFileUpload()
    {
        $publication = Publication::factory()->create();
        $user = User::factory()->create();
        $operations = [
            'operationName' => 'CreateSubmission',
            'query' => '
                mutation CreateSubmission (
                    $title: String!
                    $publication_id: ID!
                    $file_upload: [Upload!]
                    $user_id: ID!
                ) {
                    createSubmission(
                        input: {
                            title: $title,
                            publication_id: $publication_id,
                            submitters: { connect: [$user_id] },
                            files: { create: $file_upload }
                        }
                    ) {
                        title
                    }
                }
            ',
            'variables' => [
                'title' => '    Test Submission    ',
                'publication_id' => $publication->id,
                'user_id' => $user->id,
                'file_upload' => null,
            ],
        ];
        $map = [
            '0' => ['variables.file_upload'],
        ];
        $file = [
            '0' => UploadedFile::fake()->create('test.txt', 500),
        ];

        $response = $this->multipartGraphQL($operations, $map, $file);

        $response
            ->assertJsonPath('data.createSubmission.title', 'Test Submission');
    }

    /**
     * @return void
     */
    public function testUserCanOnlyBeAssignedARoleOnce()
    {
        $user = User::factory()->create();

        $submission = Submission::factory()
            ->hasAttached($user, [], 'reviewCoordinators')
            ->create();

        $this->expectException(QueryException::class);
        $submission->reviewCoordinators()->attach($user);

        $reviewCoordinators = $submission->reviewCoordinators()
            ->wherePivot('user_id', $user->id)
            ->count();

        $this->assertEquals(1, $reviewCoordinators);
    }

    /**
     * @return void
     */
    public function testUserCanOnlyBeAssignedOneRole()
    {
        $user = User::factory()->create();
        $submission = Submission::factory()
            ->hasAttached($user, [], 'reviewers')
            ->create();

        $this->expectException(QueryException::class);

        $submission->reviewCoordinators()->attach($user);
    }

    protected function executeSubmissionRoleAssignment(string $role, Submission $submission, User $user)
    {
        return $this->graphQL(
            'mutation AssignSubmissionRole ($user_id: ID!, $submission_id: ID!) {
                updateSubmission(
                    input: {
                        id: $submission_id
                        ' . $role . ': {
                        connect: [$user_id]
                        }
                    }
                ) {
                    id
                    ' . $role . ' {
                        id
                    }
                }
            }',
            [
                'submission_id' => $submission->id,
                'user_id' => $user->id,
            ]
        );
    }

    /**
     * @return array
     */
    public function allSubmissionRoles(): array
    {
        return [
            //Role
            'submitter' => ['submitters'],
            'reviewer' => ['reviewers'],
            'review_coordinator' => ['review_coordinators'],
        ];
    }

    /**
     * @dataProvider allSubmissionRoles
     * @return void
     */
    public function testApplicationAdminCanUpdateAnyUserRole($role)
    {
        $this->beAppAdmin();

        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->create([
                'title' => 'Test Submission',
            ]);
        $user = User::factory()->create();

        $response = $this->executeSubmissionRoleAssignment($role, $submission, $user);

        $response->assertJson(fn (AssertableJson $json) =>
        $json
            ->has('data', fn ($json) =>
                $json->has('updateSubmission', fn ($json) =>
                    $json->has($role, 1, fn ($json) =>
                        $json->where('id', (string)$user->id))
                    ->etc())));
    }

    /**
     * @dataProvider allSubmissionRoles
     * @return void
     */
    public function testPublicationAdminsCanUpdateTheirOwnSubmissionsRoles($role)
    {
        /** @var User $publicationAdmin */
        $publicationAdmin = User::factory()->create();
        $this->actingAs($publicationAdmin);

        $publication = Publication::factory()
            ->hasAttached($publicationAdmin, ['role_id' => Role::PUBLICATION_ADMINISTRATOR_ROLE_ID])
            ->create();

        $submission = Submission::factory()
            ->for($publication)
            ->create(['title' => 'Test submission']);

        $user = User::factory()->create();

        $response = $this->executeSubmissionRoleAssignment($role, $submission, $user);
        $response->assertJson(fn (AssertableJson $json) =>
        $json
            ->has('data', fn ($json) =>
                $json->has('updateSubmission', fn ($json) =>
                    $json->has($role, 1, fn ($json) =>
                        $json->where('id', (string)$user->id))
                    ->etc())));
    }

    /**
     * @dataProvider allSubmissionRoles
     * @return void
     */
    public function testPublicationAdminsCannotUpdateOtherPublicationSubmissions($role)
    {
        /** @var User $publicationAdmin */
        $publicationAdmin = User::factory()->create();
        $this->actingAs($publicationAdmin);

        Publication::factory()
            ->hasAttached($publicationAdmin, ['role_id' => Role::PUBLICATION_ADMINISTRATOR_ROLE_ID])
            ->create();

        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->create(['title' => 'Test submission']);

        $user = User::factory()->create();

        $response = $this->executeSubmissionRoleAssignment($role, $submission, $user);

        $response->assertJsonPath('errors.0.extensions.category', 'authorization');
    }

    public function reviewCoordinatorAssignableRolesProvider()
    {
        return [
            'review_coordinators' => ['review_coordinators', false],
            'reviewers' => ['reviewers', true],
            'submitters' => ['submitters', true],
        ];
    }

    /**
     * @dataProvider reviewCoordinatorAssignableRolesProvider
     * @return void
     */
    public function testReviewCoordinatorsCanUpdateRolesOnTheirSubmissions($role, $allowed)
    {
        /** @var User $reviewCoordinator */
        $reviewCoordinator = User::factory()->create();
        $this->actingAs($reviewCoordinator);

        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->hasAttached($reviewCoordinator, [], 'reviewCoordinators')
            ->create(['title' => 'Test submission']);

        $user = User::factory()->create();

        $response = $this->executeSubmissionRoleAssignMent($role, $submission, $user);

        if ($allowed) {
            $response->assertJsonPath('data.updateSubmission.' . $role . '.0.id', (string)$user->id);
        } else {
            $response->assertJsonPath('errors.0.extensions.category', 'authorization');
        }
    }

    /**
     * @dataProvider allSubmissionRoles
     * @return void
     */
    public function testReviewersCannotAssignRoles($role)
    {
        /** @var User $reviewer */
        $reviewer = User::factory()->create();
        $this->actingAs($reviewer);

        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->hasAttached($reviewer, [], 'reviewers')
            ->create(['title' => 'Test submission']);

        $user = User::factory()->create();

        $response = $this->executeSubmissionRoleAssignMent($role, $submission, $user);

        $response->assertJsonPath('errors.0.extensions.category', 'authorization');
    }

    /**
     * @return void
     */
    public function testSubmissionStatusCanBeRetrievedAndChangedViaEloquent()
    {
        $submission = Submission::factory()->create();
        $this->assertEquals(Submission::INITIALLY_SUBMITTED, $submission->status);
        $this->assertEquals('INITIALLY_SUBMITTED', $submission->status_name);
        $submission->status = Submission::AWAITING_REVIEW;
        $this->assertEquals(Submission::AWAITING_REVIEW, $submission->status);
        $this->assertEquals('AWAITING_REVIEW', $submission->status_name);
    }
}
