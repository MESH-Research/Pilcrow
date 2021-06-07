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
        Publication::factory()->count(6)->create();
        Submission::factory()->count(10)->for($publication)->create();
        Submission::factory()->count(16)->create();
        $this->assertEquals(1, Submission::where('publication_id', $publication->id)
            ->pluck('publication_id')->unique()->count());
        $this->assertGreaterThanOrEqual(10, $publication->submissions->count());
        $this->assertLessThanOrEqual(26, $publication->submissions->count());
    }

    /**
     * @return void
     * @doesNotPerformAssertions
     */
    public function testThatSubmissionsHaveAManyToManyRelationshipWithUsers()
    {
        $publication = Publication::factory()->create([
            'name' => 'Test Publication #2',
        ]);
        $users = User::factory()->count(6)->create();
        $submissions = Submission::factory()
            ->count(4)
            ->hasAttached(
                $users,
                [
                    'role_id' => Role::whereIn('name', [
                        Role::REVIEW_COORDINATOR,
                        Role::REVIEWER,
                        Role::SUBMITTER,
                    ])->get()->pluck('id')->random(),
                ]
            )
            ->create();
        print_r($submissions->toArray());
    }
}
