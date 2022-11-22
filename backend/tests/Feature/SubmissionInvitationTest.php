<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Submission;
use App\Models\SubmissionInvitation;
use App\Models\User;
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
    // public function testDefaultDataIsProvidedUponSubmissionInvitationCreation()
    // {
    //     $this->beAppAdmin();
    //     $submission = Submission::factory()->create();
    //     $invite = SubmissionInvitation::create(['email' => $this->faker->email(), 'submission_id' => $submission->id]);
    //     $this->assertEquals(36, strlen($invite->token));
    //     $this->assertNotNull($invite->expiration);
    // }

    public function testFiltering()
    {
        $users = User::factory()->count(3)->create();
        // $first_user = $users->first();
        // $last_user = $users->last();
        $submission = Submission::factory()
            ->hasAttached($users, [], 'reviewers')
            ->create();
        $second_user = $users->splice(1,1)->first();
        $filtered = $submission->reviewers->filter(function($value) use ($second_user) {
            return $value->email !== $second_user->email;
        });
        print_r($filtered->toArray());
    }
}
