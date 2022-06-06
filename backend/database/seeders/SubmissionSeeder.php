<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Events\SubmissionCreated;
use App\Listeners\NotifyUsersAboutCreatedSubmission;
use App\Models\Submission;
use App\Models\SubmissionContent;
use App\Models\User;
use Illuminate\Database\Seeder;

class SubmissionSeeder extends Seeder
{
    /**
     * Seed a submission with the following roles:
     * - Submitter: regularUser
     * - Review Coordinator: reviewCoordinator
     *
     * @param int $id
     * @param string $title
     * @return void
     */
    public function run($id, $title)
    {
        $submission = Submission::factory()
            ->hasAttached(
                User::where('username', 'regularUser')->firstOrFail(),
                [],
                'submitters'
            )
            ->hasAttached(
                User::where('username', 'reviewCoordinator')->firstOrFail(),
                [],
                'reviewCoordinators'
            )
            ->has(SubmissionContent::factory()->count(3), 'contentHistory')
            ->create([
                'id' => $id,
                'title' => $title,
                'publication_id' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ]);
        $submission->updated_by = 2;
        $submission->content()->associate($submission->contentHistory->last())->save();
        $event = new SubmissionCreated($submission);
        $listener = new NotifyUsersAboutCreatedSubmission();
        $listener->handle($event);
    }
}
