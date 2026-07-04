<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Auth\Roles\ScopedRole;
use App\Exceptions\ClientException;
use App\Models\Submission;
use App\Models\SubmissionInvitation;

final class InviteSubmissionUser
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
    public function inviteReviewer($_, array $args)
    {
        $submission = Submission::where('id', $args['submission_id'])->firstOrFail();
        $invite = SubmissionInvitation::create([
            'submission_id' => $submission->id,
            'role' => ScopedRole::Reviewer->toSlug(),
            'role_id' => ScopedRole::Reviewer->legacyId(),
            'email' => $args['email'],
            'message' => $args['message'] ?? null,
        ]);

        return $invite->inviteReviewer();
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
    public function inviteReviewCoordinator($_, array $args)
    {
        $submission = Submission::where('id', $args['submission_id'])->firstOrFail();
        $this->guardAgainstExistingCoordinator($submission);
        $invite = SubmissionInvitation::create([
            'submission_id' => $submission->id,
            'role' => ScopedRole::ReviewCoordinator->toSlug(),
            'role_id' => ScopedRole::ReviewCoordinator->legacyId(),
            'email' => $args['email'],
            'message' => $args['message'] ?? null,
        ]);

        return $invite->inviteReviewCoordinator();
    }

    /**
     * A submission may have at most one review coordinator. Reject the invite if
     * one already exists, rather than silently creating an invalid
     * multi-coordinator state (the pivot has no DB constraint enforcing this).
     *
     * @param \App\Models\Submission $submission
     * @return void
     * @throws \App\Exceptions\ClientException
     */
    private function guardAgainstExistingCoordinator(Submission $submission): void
    {
        if ($submission->reviewCoordinators()->exists()) {
            throw new ClientException(
                'This submission already has a review coordinator.',
                'submissionInvitation',
                'SUBMISSION_ALREADY_HAS_COORDINATOR'
            );
        }
    }
}
