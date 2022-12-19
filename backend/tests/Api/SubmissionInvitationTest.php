<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\Role;
use App\Models\User;
use App\Models\Submission;
use App\Models\SubmissionInvitation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
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
     * @return array
     */
    public function provideInvalidEmails(): array
    {
        return [
            'no domain' => ['invalidemail'],
            'fake domain' => ['test@madeupdomainfortesting.com'],
            'two @ symbols' => ['test@test@gmail.com'],
        ];
    }

    /**
     * @dataProvider provideInvalidEmails
     * @return void
     */
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
        $response->assertJsonPath('errors.0.extensions.category', 'validation');
    }

    /**
     * @param array $variables
     * @return \Illuminate\Testing\TestResponse
     */
    public function callAcceptSubmissionInvite(array $variables): \Illuminate\Testing\TestResponse
    {
        return $this->graphQL('
            mutation AcceptSubmissionInvite(
                $uuid: String!
                $token: String!
                $expires: String!
                $name: String
                $username: String!
                $password: String!
            ) {
                acceptSubmissionInvite(
                    uuid: $uuid
                    token: $token
                    expires: $expires
                    user: { name: $name, username: $username, password: $password }
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
            'role_id' => Role::REVIEWER_ROLE_ID,
            'email' => 'mesh@msu.edu',
        ]);
        $invite->inviteReviewer();

        $expires = (string)Carbon::now()->addMinutes(10)->timestamp;
        $token = $invite->makeToken($expires);

        $params = [
            'uuid' => $invite->uuid,
            'token' => $token,
            'expires' => $expires,
            'name' => "",
            'username' => "MeshReviewer",
            'password' => "ImTheMeshReviewerAndThisIsMyPassword!@#",
        ];

        $response = $this->callAcceptSubmissionInvite($params);

        $this->assertNotNull(Arr::get($response, 'data.acceptSubmissionInvite'));
        $this->assertNull(Arr::get($response, 'errors'));
    }
}
