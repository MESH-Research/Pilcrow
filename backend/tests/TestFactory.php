<?php
declare(strict_types=1);

namespace Tests;

use App\Models\InlineComment;
use App\Models\OverallComment;
use App\Models\StyleCriteria;
use App\Models\Submission;
use App\Models\User;

trait TestFactory
{
    /**
     * @param int $status (default: Submission::UNDER_REVIEW)
     * @return Submission
     */
    protected function createSubmission($status = Submission::UNDER_REVIEW)
    {
        $user = User::factory()->create();

        return Submission::factory()
            ->hasAttached($user, [], 'submitters')
            ->create(['status' => $status]);
    }

    /**
     * @param int $id
     * @return StyleCriteria
     */
    protected function createStyleCriteria($id)
    {
        $criteria = StyleCriteria::factory()
            ->create([
                'name' => 'PHPUnit Criteria',
                'publication_id' => $id,
                'description' => 'This is a test style criteria created by PHPUnit',
                'icon' => 'php',
            ]);

        return $criteria;
    }

    /**
     * @param int $count
     * @param User|null $user (optional)
     * @return Submission
     */
    protected function createSubmissionWithInlineComment($count = 1, $user = null)
    {
        if ($user === null) {
            $user = User::factory()->create();
        }
        $submission = $this->createSubmission();
        $style_criteria = $this->createStyleCriteria($submission->publication->id);
        InlineComment::factory()->count($count)->create([
            'submission_id' => $submission->id,
            'content' => 'This is some content for an inline comment created by PHPUnit.',
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'style_criteria' => [$style_criteria],
        ]);

        return $submission;
    }

    /**
     * @param int $count
     * @param User|null $user (optional)
     * @return Submission
     */
    protected function createSubmissionWithOverallComment($count = 1, $user = null)
    {
        if ($user === null) {
            $user = User::factory()->create();
        }
        $submission = $this->createSubmission();
        OverallComment::factory()->count($count)->create([
            'submission_id' => $submission->id,
            'content' => 'This is some content for an overall comment created by PHPUnit.',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return $submission;
    }
}
