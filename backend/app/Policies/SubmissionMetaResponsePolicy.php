<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;

class SubmissionMetaResponsePolicy
{
    /**
     * Determine if the user can update the submission meta response.
     *
     * @param \App\Models\User|null $user The user attempting to update the response.
     * @param array $input The input data containing the submission ID.
     * @return bool True if the user can update the response, false otherwise.
     */
    public function update(User $user, array $input): bool
    {
        $submissionId = $input['input']['submission_id'];
        if (!$user) {
            return false;
        }

        // Fetch the submission using the user as a filter.
        $submission = Submission::whereSubmitter($user->id)->find($submissionId);

        if (!$submission) {
            return false;
        }

        if ($submission->status !== Submission::DRAFT) {
            return false;
        }

        return true;
    }
}
