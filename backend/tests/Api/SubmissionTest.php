<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\Publication;
use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\ApiTestCase;

class SubmissionTest extends ApiTestCase
{
    use RefreshDatabase;

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
                    created_by {
                        id
                    }
                    updated_by {
                        id
                    }
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
        /**
         * @var User
         */
        $user = User::factory()->create();
        $this->actingAs($user);

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
            ->hasAttached($publicationAdmin, [], 'publicationAdmins')
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
     * @dataProvider allSubmissionRoles
     * @param string $role
     * @return void
     */
    public function testMyRoleFields(string $role): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        $submission = Submission::factory()->create();
        $camelized = Str::camel($role);
        $submission->$camelized()->attach($user);
        $graphql = '
            query GetSubmission($id: ID!) {
                submission(id: $id) {
                    my_role
                    effective_role
                }
            }
        ';

        $response = $this->graphQL($graphql, ['id' => $submission->id]);
        $response
            ->assertJsonPath('data.submission.my_role', Str::singular($role))
            ->assertJsonPath('data.submission.effective_role', Str::singular($role));
    }

    public function testAdminGetsEffectiveRole()
    {
        $this->beAppAdmin();
        $submission = Submission::factory()->create();
        $gql = '
            query GetSubmission($id: ID!) {
                submission(id: $id) {
                    my_role
                    effective_role
                }
            }
        ';

        $this->graphQL($gql, ['id' => $submission->id])
            ->assertJsonPath('data.submission.my_role', null)
            ->assertJsonPath('data.submission.effective_role', 'review_coordinator');
    }

    public function testPublicationRoleGetsEffectiveRole()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        $submission = Submission::factory()->create();
        $submission->publication->publicationAdmins()->attach($user);
        $gql = '
            query GetSubmission($id: ID!) {
                submission(id: $id) {
                    my_role
                    effective_role
                }
            }
        ';

        $this->graphQL($gql, ['id' => $submission->id])
            ->assertJsonPath('data.submission.my_role', null)
            ->assertJsonPath('data.submission.effective_role', 'review_coordinator');
    }

    public function testCreatedByFieldIsSet()
    {
        //TODO: Once file uploads are no longer required at initial submission creation, remove file upload portion of this test.
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $publication = Publication::factory()->create();

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
                        created_by {
                            id
                        }
                        updated_by {
                            id
                        }
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
            ->assertJsonPath('data.createSubmission.created_by.id', (string)$user->id);
        $response
            ->assertJsonPath('data.createSubmission.updated_by.id', (string)$user->id);
    }

    public function testUpdatedByFieldIsSet()
    {
        $submission = Submission::factory()->create();

        $user = $this->beAppAdmin();

        $response = $this->graphQL(
            'mutation updateSubmission($id: ID!, $title: String) {
                updateSubmission(input: {
                    id: $id
                    title: $title
                }) {
                    created_by {
                        id
                    }
                    updated_by {
                        id
                    }
                }
            }',
            [
                'id' => $submission->id,
                'title' => 'A new title',
            ]
        );

        $response->assertJsonPath('data.updateSubmission.created_by.id', (string)$submission->created_by);
        $response->assertJsonPath('data.updateSubmission.updated_by.id', (string)$user->id);
    }

    /**
     * @param Submission $submission
     * @param string $status
     * @return void
     */
    protected function executeSubmissionStatusUpdateSuccessfully(Submission $submission, string $status)
    {
        $response = $this->graphQL(
            'mutation UpdateSubmissionStatus ($submission_id: ID!, $status: SubmissionStatus) {
                updateSubmission(
                    input: {
                        id: $submission_id
                        status: $status
                    }
                ) {
                    id
                    status
                }
            }',
            [
                'submission_id' => $submission->id,
                'status' => $status,
            ]
        );
        $expected_data = [
            'updateSubmission' => [
                'id' => (string)$submission->id,
                'status' => $status,
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @param Submission $submission
     * @param string $status
     * @return void
     */
    protected function executeSubmissionStatusUpdateUnsuccessfully(Submission $submission, string $status)
    {
        $response = $this->graphQL(
            'mutation UpdateSubmissionStatus ($submission_id: ID!, $status: SubmissionStatus) {
                updateSubmission(
                    input: {
                        id: $submission_id
                        status: $status
                    }
                ) {
                    id
                    status
                }
            }',
            [
                'submission_id' => $submission->id,
                'status' => $status,
            ]
        );
        $response->assertJsonPath('errors.0.extensions.category', 'authorization');
    }

    /**
     * @return array
     */
    public function provideAllSubmissionStates(): array
    {
        return [
            'Initially Submitted' => [
                'INITIALLY_SUBMITTED',
            ],
            'Awaiting Resubmission' => [
                'RESUBMISSION_REQUESTED',
            ],
            'Resubmitted' => [
                'RESUBMITTED',
            ],
            'Awaiting Review' => [
                'AWAITING_REVIEW',
            ],
            'Rejected' => [
                'REJECTED',
            ],
            'Accepted as Final' => [
                'ACCEPTED_AS_FINAL',
            ],
            'Expired' => [
                'EXPIRED',
            ],
            'Under Review' => [
                'UNDER_REVIEW',
            ],
            'Awaiting Decision' => [
                'AWAITING_DECISION',
            ],
            'Revision Requested' => [
                'REVISION_REQUESTED',
            ],
            'Archived' => [
                'ARCHIVED',
            ],
            'Deleted' => [
                'DELETED',
            ],
        ];
    }

    /**
     * @dataProvider provideAllSubmissionStates
     * @return void
     */
    public function testApplicationAdminCanUpdateSubmissionStatus(string $status)
    {
        $this->beAppAdmin();
        $submission = Submission::factory()->create();
        $this->executeSubmissionStatusUpdateSuccessfully($submission, $status);
    }

    /**
     * @dataProvider provideAllSubmissionStates
     * @return void
     */
    public function testPublicationAdminCanUpdateSubmissionStatus(string $status)
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $publication = Publication::factory()
            ->hasAttached($admin, [], 'publicationAdmins')
            ->create();
        $submission = Submission::factory()
            ->for($publication)
            ->create(['title' => 'Test submission']);
        $this->executeSubmissionStatusUpdateSuccessfully($submission, $status);
    }

    /**
     * @dataProvider provideAllSubmissionStates
     * @return void
     */
    public function testEditorCanUpdateSubmissionStatus(string $status)
    {
        /** @var User $editor */
        $editor = User::factory()->create();
        $this->actingAs($editor);
        $publication = Publication::factory()
            ->hasAttached($editor, [], 'editors')
            ->create();
        $submission = Submission::factory()
            ->for($publication)
            ->create(['title' => 'Test submission']);
        $this->executeSubmissionStatusUpdateSuccessfully($submission, $status);
    }

    /**
     * @dataProvider provideAllSubmissionStates
     * @return void
     */
    public function testReviewCoordinatorCanUpdateSubmissionStatus(string $status)
    {
        /** @var User $reviewCoordinator */
        $reviewCoordinator = User::factory()->create();
        $this->actingAs($reviewCoordinator);

        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->hasAttached($reviewCoordinator, [], 'reviewCoordinators')
            ->create(['title' => 'Test submission']);
        $this->executeSubmissionStatusUpdateSuccessfully($submission, $status);
    }

    /**
     * @dataProvider provideAllSubmissionStates
     * @return void
     */
    public function testReviewerCannotUpdateSubmissionStatus(string $status)
    {
        /** @var User $reviewer */
        $reviewer = User::factory()->create();
        $this->actingAs($reviewer);

        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->hasAttached($reviewer, [], 'reviewers')
            ->create(['title' => 'Test submission']);
        $this->executeSubmissionStatusUpdateUnsuccessfully($submission, $status);
    }

    /**
     * @dataProvider provideAllSubmissionStates
     * @return void
     */
    public function testSubmitterCannotUpdateSubmissionStatus(string $status)
    {
        /** @var User $reviewer */
        $reviewer = User::factory()->create();
        $this->actingAs($reviewer);

        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->hasAttached($reviewer, [], 'reviewers')
            ->create(['title' => 'Test submission']);
        $this->executeSubmissionStatusUpdateUnsuccessfully($submission, $status);
    }
}
