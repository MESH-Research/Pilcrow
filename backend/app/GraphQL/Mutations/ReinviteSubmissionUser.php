<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Auth\ScopedRole;
use App\Models\Submission;
use App\Models\SubmissionInvitation;

final class ReinviteSubmissionUser
{
    /**
     * Check that a submission exists at the supplied submission ID, and send an
     * invitation with an optional message to the supplied email inviting them to
     * be a reviewer
     *
     * @param null  $_
     * @param array{submission_id: int, email: string, message?: string}  $args
     * @return \App\Models\Submission
     */
    public function reinviteReviewer($_, array $args)
    {
        $submission = Submission::where('id', $args['submission_id'])->firstOrFail();
        $invite = SubmissionInvitation::create([
            'submission_id' => $submission->id,
            'role_id' => ScopedRole::Reviewer->pivotValue(),
            'email' => $args['email'],
            'message' => $args['message'] ?? null,
        ]);

        return $invite->reinviteReviewer();
    }

    /**
     * Check that a submission exists at the supplied submission ID, and send an
     * invitation with an optional message to the supplied email inviting them to
     * be a review coordinator
     *
     * @param null  $_
     * @param array{"submission_id": int, "email": string, "message"?: string}  $args
     * @return \App\Models\Submission
     */
    public function reinviteReviewCoordinator($_, array $args)
    {
        $submission = Submission::where('id', $args['submission_id'])->firstOrFail();
        $invite = SubmissionInvitation::create([
            'submission_id' => $submission->id,
            'role_id' => ScopedRole::ReviewCoordinator->pivotValue(),
            'email' => $args['email'],
            'message' => $args['message'] ?? null,
        ]);

        return $invite->reinviteReviewCoordinator();
    }
}
