<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Submission;
use App\Models\SubmissionInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SubmissionInvitationTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testDefaultDataIsProvidedUponSubmissionInvitationCreation()
    {
        $submission = Submission::factory()->create();
        $invite = SubmissionInvitation::create(['email' => $this->faker->email(), 'submission_id' => $submission->id]);
        $this->assertEquals(36, strlen($invite->token));
        $this->assertNotNull($invite->expiration);
    }
}
