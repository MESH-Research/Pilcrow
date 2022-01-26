<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Events\SubmissionCreated;
use App\Listeners\NotifyUsersAboutCreatedSubmission;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;

class SubmissionSeeder extends Seeder
{
    /**
     * Seed a submission with the following roles:
     * - Submitter: regularUser
     * - Review Coordinator: supplied user
     *
     * @param \App\Models\User $review_coordinator
     * @return void
     */
    public function run($review_coordinator)
    {
        $submission = Submission::factory()
            ->hasAttached(
                User::where('username', 'regularUser')->firstOrFail(),
                [
                    'role_id' => 6,
                ]
            )
            ->hasAttached(
                $review_coordinator,
                [
                    'role_id' => 4,
                ]
            )
            ->create([
                'id' => 100,
                'title' => 'CCR Test Submission 1',
                'publication_id' => 1,
            ]);

        $event = new SubmissionCreated($submission);
        $listener = new NotifyUsersAboutCreatedSubmission();
        $listener->handle($event);
    }
}
