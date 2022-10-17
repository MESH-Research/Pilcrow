<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Submission;

final class CreateStagedUser
{
    /**
     * Create a staged user and attach them as a reviewer to a submisison
     *
     * @param  null  $_
     * @param  array{}  $args
     * @return \App\Models\Submission
     */
    public function stageReviewer($_, array $args)
    {
        $submission = Submission::where('id', $args['submission_id'])->firstOrFail();
        $submission->stageReviewer($args['email']);
        $submission->sendInvitation('reviewer', $args['email']);

        return $submission;
    }

    /**
     * Create a staged user and attach them as a review coordinator to a submisison
     *
     * @param  null  $_
     * @param  array{}  $args
     * @return \App\Models\Submission
     */
    public function stageReviewCoordinator($_, array $args)
    {
        $submission = Submission::where('id', $args['submission_id'])->firstOrFail();
        $submission->stageReviewCoordinator($args['email']);

        return $submission;
    }
}
