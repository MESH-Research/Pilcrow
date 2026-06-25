<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Auth\Roles\ScopedRole;
use App\Models\Submission;
use App\Models\SubmissionInvitation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\ApiTestCase;

class SubmissionInvitationTest extends ApiTestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testValidEmailsCanBeProvidedInAnInvitation()
    {
        $valid_email = 'test@gmail.com';
        $this->beAppAdmin();
        $submission = Submission::factory()->create();
        $response = $this->graphQL(
            'mutation InviteReviewer ($submission_id: ID! $email: String! $message: String){
                inviteReviewer(input: {
                  submission_id: $submission_id
                  email: $email
                  message: $message
                }) {
                  id
                  reviewers {
                    email
                    staged
                  }
                }
              }
            ',
            [
                'submission_id' => $submission->id,
                'email' => $valid_email,
                'message' => '',
            ]
        );
        $expected_data = [
            'inviteReviewer' => [
                'id' => (string)$submission->id,
                'reviewers' => [
                    [
                        'email' => $valid_email,
                        'staged' => true,
                    ],
                ],
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * Inviting a review coordinator stages the user, attaches them with the
     * review_coordinator role, and persists the invitation with the slug (and
     * its legacy id for rollback safety).
     *
     * @return void
     */
    public function testReviewCoordinatorCanBeInvited()
    {
        $valid_email = 'coordinator@gmail.com';
        $this->beAppAdmin();
        $submission = Submission::factory()->create();
        $response = $this->graphQL(
            'mutation InviteReviewCoordinator ($submission_id: ID! $email: String! $message: String){
                inviteReviewCoordinator(input: {
                  submission_id: $submission_id
                  email: $email
                  message: $message
                }) {
                  id
                  review_coordinators {
                    email
                    staged
                  }
                }
              }
            ',
            [
                'submission_id' => $submission->id,
                'email' => $valid_email,
                'message' => '',
            ]
        );

        $response->assertJsonPath('data.inviteReviewCoordinator.review_coordinators', [
            ['email' => $valid_email, 'staged' => true],
        ]);
        $this->assertDatabaseHas('submission_invitations', [
            'submission_id' => $submission->id,
            'email' => $valid_email,
            'role' => ScopedRole::ReviewCoordinator->toSlug(),
            'role_id' => ScopedRole::ReviewCoordinator->legacyId(),
        ]);
    }

    /**
     * Re-inviting a reviewer resends the invitation without staging a duplicate
     * user and records the reviewer role slug + legacy id.
     *
     * @return void
     */
    public function testReviewerCanBeReinvited()
    {
        $valid_email = 'reinvited.reviewer@gmail.com';
        $this->beAppAdmin();
        $submission = Submission::factory()->create();

        // Reinvite resends to an already-staged reviewer, so invite first.
        SubmissionInvitation::create([
            'submission_id' => $submission->id,
            'role' => ScopedRole::Reviewer->toSlug(),
            'email' => $valid_email,
        ])->inviteReviewer();

        $response = $this->graphQL(
            'mutation ReinviteReviewer ($submission_id: ID! $email: String! $message: String){
                reinviteReviewer(input: {
                  submission_id: $submission_id
                  email: $email
                  message: $message
                }) {
                  id
                }
              }
            ',
            [
                'submission_id' => $submission->id,
                'email' => $valid_email,
                'message' => '',
            ]
        );

        $response->assertJsonPath('data.reinviteReviewer.id', (string)$submission->id);
        $this->assertDatabaseHas('submission_invitations', [
            'submission_id' => $submission->id,
            'email' => $valid_email,
            'role' => ScopedRole::Reviewer->toSlug(),
            'role_id' => ScopedRole::Reviewer->legacyId(),
        ]);
    }

    /**
     * Re-inviting a review coordinator resends the invitation and records the
     * review_coordinator role slug + legacy id.
     *
     * @return void
     */
    public function testReviewCoordinatorCanBeReinvited()
    {
        $valid_email = 'reinvited.coordinator@gmail.com';
        $this->beAppAdmin();
        $submission = Submission::factory()->create();

        // Reinvite resends to an already-staged coordinator, so invite first.
        SubmissionInvitation::create([
            'submission_id' => $submission->id,
            'role' => ScopedRole::ReviewCoordinator->toSlug(),
            'email' => $valid_email,
        ])->inviteReviewCoordinator();

        $response = $this->graphQL(
            'mutation ReinviteReviewCoordinator ($submission_id: ID! $email: String! $message: String){
                reinviteReviewCoordinator(input: {
                  submission_id: $submission_id
                  email: $email
                  message: $message
                }) {
                  id
                }
              }
            ',
            [
                'submission_id' => $submission->id,
                'email' => $valid_email,
                'message' => '',
            ]
        );

        $response->assertJsonPath('data.reinviteReviewCoordinator.id', (string)$submission->id);
        $this->assertDatabaseHas('submission_invitations', [
            'submission_id' => $submission->id,
            'email' => $valid_email,
            'role' => ScopedRole::ReviewCoordinator->toSlug(),
            'role_id' => ScopedRole::ReviewCoordinator->legacyId(),
        ]);
    }

    /**
     * @return array
     */
    public static function invalidEmailsProvider(): array
    {
        return [
            'no domain' => ['invalidemail'],
            'fake domain' => ['test@madeupdomainfortesting.com'],
            'two @ symbols' => ['test@test@gmail.com'],
        ];
    }

    /**
     * @return void
     */
    #[DataProvider('invalidEmailsProvider')]
    public function testInvalidEmailsCannotBeProvidedInAnInvitation(string $email)
    {
        $this->beAppAdmin();
        $submission = Submission::factory()->create();
        $response = $this->graphQL(
            'mutation InviteReviewer ($submission_id: ID! $email: String! $message: String){
                inviteReviewer(input: {
                  submission_id: $submission_id
                  email: $email
                  message: $message
                }) {
                  id
                  reviewers {
                    email
                    staged
                  }
                }
              }
            ',
            [
                'submission_id' => $submission->id,
                'email' => $email,
                'message' => '',
            ]
        );
        $responseMessage = $response->json('errors.0.message');
        $this->assertStringStartsWith('Validation failed', $responseMessage);
    }

    /**
     * @param array $variables
     * @return \Illuminate\Testing\TestResponse
     */
    public function callAcceptSubmissionInvite(array $variables): TestResponse
    {
        return $this->graphQL('
            mutation AcceptSubmissionInvite(
                $uuid: String!
                $token: String!
                $expires: String!
                $id: ID!
                $name: String
                $username: String!
                $password: String!
            ) {
                acceptSubmissionInvite(
                    uuid: $uuid
                    token: $token
                    expires: $expires
                    user: { id: $id, name: $name, username: $username, password: $password }
                ) {
                    id
                    name
                    email
                    username
                }
            }
        ', $variables);
    }

    /**
     * @return void
     */
    public function testCanAcceptAnInviteToASubmission(): void
    {
        $this->beAppAdmin();
        $submission = Submission::factory()->create();
        $invite = SubmissionInvitation::create([
            'submission_id' => $submission->id,
            'role' => ScopedRole::Reviewer->toSlug(),
            'email' => 'mesh@msu.edu',
        ]);
        $invite->inviteReviewer();

        $expires = (string)Carbon::now()->addMinutes(10)->timestamp;
        $token = $invite->makeToken($expires);

        $params = [
            'uuid' => $invite->uuid,
            'token' => $token,
            'expires' => $expires,
            'id' => $invite->invitee->id,
            'name' => '',
            'username' => 'MeshReviewer',
            'password' => 'ImTheMeshReviewerAndThisIsMyPassword!@#',
        ];

        $response = $this->callAcceptSubmissionInvite($params);

        $this->assertNotNull(Arr::get($response, 'data.acceptSubmissionInvite'));
        $this->assertNull(Arr::get($response, 'errors'));
    }

    /**
     * @return array
     */
    public static function variablesProvider(): array
    {
        return [
            'missing username' => [
                [
                    'username' => '',
                ],
            ],
            'missing password' => [
                [
                    'password' => '',
                ],
            ],
            'weak password' => [
                [
                    'password' => 'password123',
                ],
            ],
            'missing expires' => [
                [
                    'expires' => '',
                ],
            ],
            'expired' => [
                [
                    'expires' => (string)Carbon::now()->subMinutes(30)->timestamp,
                ],
            ],
            'missing uuid' => [
                [
                    'uuid' => '',
                ],
            ],
            'invalid uuid' => [
                [
                    'uuid' => '1234567890',
                ],
            ],
            'incorrect uuid' => [
                [
                    'uuid' => Str::uuid()->toString(),
                ],
            ],
            'missing token' => [
                [
                    'token' => '',
                ],
            ],
            'invalid token' => [
                [
                    'token' => '1234567890',
                ],
            ],
            'incorrect token' => [
                [
                    'token' => hash_hmac(
                        'sha256',
                        '20000#email@msu.edu#1671768239',
                        'APP_KEY',
                    ),
                ],
            ],
        ];
    }

    /**
     * @param array $variable
     * @return void
     */
    #[DataProvider('variablesProvider')]
    public function testBadInputsForTheAcceptanceMutationResultInFailures(array $variable): void
    {
        $this->beAppAdmin();
        $submission = Submission::factory()->create();
        $invite = SubmissionInvitation::create([
            'submission_id' => $submission->id,
            'role' => ScopedRole::Reviewer->toSlug(),
            'email' => 'mesh@msu.edu',
        ]);
        $invite->inviteReviewer();

        $expires = $variable['expires'] ?? (string)Carbon::now()->addMinutes(10)->timestamp;
        $token = $variable['token'] ?? $invite->makeToken($expires);

        $params = [
            'uuid' => $variable['uuid'] ?? $invite->uuid,
            'token' => $variable['token'] ?? $token,
            'expires' => $variable['expires'] ?? $expires,
            'id' => $variable['id'] ?? $invite->invitee->id,
            'name' => '',
            'username' => $variable['username'] ?? 'MeshReviewer',
            'password' => $variable['password'] ?? 'ImTheMeshReviewerAndThisIsMyPassword!@#',
        ];

        $response = $this->callAcceptSubmissionInvite($params);

        $this->assertNotNull(Arr::get($response, 'errors'));
    }
}
