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
     * @param int $id
     * @param string $title
     * @return void
     */
    public function run($id, $title)
    {
        $submission = Submission::factory()
            ->hasAttached(
                User::where('username', 'regularUser')->firstOrFail(),
                [
                    'role_id' => 6,
                ]
            )
            ->hasAttached(
                User::where('username', 'reviewCoordinator')->firstOrFail(),
                [
                    'role_id' => 4,
                ]
            )
            ->create([
                'id' => $id,
                'title' => $title,
                'publication_id' => 1,
            ]);

        $event = new SubmissionCreated($submission);
        $listener = new NotifyUsersAboutCreatedSubmission();
        $listener->handle($event);
    }
}
