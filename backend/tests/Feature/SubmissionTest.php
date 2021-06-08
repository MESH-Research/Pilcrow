<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testThatSubmissionsHaveAOneToManyRelationshipWithPublications()
    {
        $publication = Publication::factory()->create([
            'name' => 'Test Publication #1',
        ]);
        Publication::factory()->count(5)->create();
        $submissions = Submission::factory()->count(10)->for($publication)->create();
        Submission::factory()->count(16)->create();
        $this->assertEquals(1, $submissions->pluck('publication_id')->unique()->count());
        $this->assertGreaterThanOrEqual(10, $publication->submissions->count());
        $this->assertLessThanOrEqual(26, $publication->submissions->count());
    }

    /**
     * @return void
     */
    public function testThatSubmissionsHaveAManyToManyRelationshipWithUsers()
    {
        $submission_count = 4;
        $user_count = 6;
        $submitter_id = Role::where('name', Role::SUBMITTER)->first()->id;
        $publication = Publication::factory()->create([
            'name' => 'Test Publication #2',
        ]);
        $users = User::factory()->count($user_count)->create();

        // Create submissions and attach them to users randomly with random roles
        for ($i = 0; $i < $submission_count; $i++) {
            $random_role_id = Role::whereIn('name',
                [
                    Role::REVIEW_COORDINATOR,
                    Role::REVIEWER,
                    Role::SUBMITTER,
                ])
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
            if ($random_role_id !== $submitter_id) {
                $submission->users()->attach(
                    $users->random(),
                    [
                        'role_id' => $submitter_id,
                    ]
                );
            }
        }
        Submission::all()->map(function($submission) use ($user_count) {
            $this->assertNotEmpty($submission->users);
            $this->assertGreaterThan(0, $submission->users->count());
            $this->assertLessThanOrEqual($user_count, $submission->users->count());
        });
        User::all()->map(function($user) use ($submission_count) {
            $this->assertLessThanOrEqual($submission_count, $user->submissions->count());
        });
    }
}
