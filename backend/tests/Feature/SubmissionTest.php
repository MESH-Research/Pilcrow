<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
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

    /**
     * @return void
     */
    public function testSubmissionStatusCanBeRetrievedAndChanged()
    {
        $submission = Submission::factory()->create();
        $this->assertEquals(Submission::INITIALLY_SUBMITTED, $submission->status);
        $this->assertEquals('INITIALLY_SUBMITTED', $submission->status_name);
        $submission->status = Submission::AWAITING_REVIEW;
        $this->assertEquals(Submission::AWAITING_REVIEW, $submission->status);
        $this->assertEquals('AWAITING_REVIEW', $submission->status_name);
    }

    /**
     * @return void
     */
    public function testChanges()
    {
        $this->beAppAdmin();
        $submission = Submission::factory()->create();
        $submission->title = 'New Title';
        $submission->status = 3;
        $submission->save();
        $changes = $submission->getChanges();
        $inarray = array_key_exists('status', $changes);

        $this->assertEquals($inarray, 1);
    }
}
