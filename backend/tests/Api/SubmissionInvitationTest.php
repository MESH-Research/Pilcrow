<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class SubmissionInvitationTest extends ApiTestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testValidEmailsCanBeProvidedInAnInvitation()
    {
        $valid_email = "test@gmail.com";
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
}
