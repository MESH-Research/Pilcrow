<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Submission;
use App\Models\User;

class SubmissionMetaResponsePolicy
{
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
