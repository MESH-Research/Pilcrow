<?php
declare(strict_types=1);

namespace Database\Seeders;

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
     * @return void
     */
    public function run()
    {
        $this->callOnce(PublicationSeeder::class);
        $this->callOnce(UserSeeder::class);

        $submission_1 = $this->createSubmission(100, 'CCR Test Submission 1');
        $submission_1->status = Submission::AWAITING_REVIEW;
        $submission_1->updated_by = 1;
        $submission_1->save();
        $this->createSubmission(101, 'CCR Test Submission 2');
    }

    /**
     * Create a submission
     *
     * @param int $id
     * @param string $title
     * @return \Database\Seeders\App\Models\Submission
     */
    protected function createSubmission(int $id, string $title)
    {
        $submission = Submission::factory()
            ->hasAttached(
                User::firstWhere('username', 'regularUser'),
                [],
                'submitters'
            )
            ->hasAttached(
                User::firstWhere('username', 'reviewCoordinator'),
                [],
                'reviewCoordinators'
            )
            ->hasAttached(
                User::firstWhere('username', 'reviewer'),
                [],
                'reviewers'
            )
            ->has(SubmissionContent::factory()->count(3), 'contentHistory')
            ->create([
                'id' => $id,
                'title' => $title,
                'publication_id' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ]);
        $submission->status = Submission::INITIALLY_SUBMITTED;
        $submission->updated_by = 2;
        $submission->content()->associate($submission->contentHistory->last())->save();

        return $submission;
    }
}
