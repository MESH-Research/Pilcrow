<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Submission;
use App\Notifications\InviteReviewCoordinator;
use App\Notifications\InviteReviewer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

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
        $reviewer = $submission->stageReviewer($args['email']);
        $auth_user = Auth::user();
        $notification_data = [
            'submission' => [
                'id' => $submission->id,
            ],
            'inviter' => [
                'name' => $auth_user->name,
                'username' => $auth_user->username,
            ],
            'message' => $args['message'],
        ];
        Notification::send(
            $reviewer,
            new InviteReviewer($notification_data)
        );

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
        $review_coordinator = $submission->stageReviewCoordinator($args['email']);
        $auth_user = Auth::user();
        $notification_data = [
            'submission' => [
                'id' => $submission->id,
            ],
            'inviter' => [
                'name' => $auth_user->name,
                'username' => $auth_user->username,
            ],
            'message' => $args['message'],
        ];
        Notification::send(
            $review_coordinator,
            new InviteReviewCoordinator($notification_data)
        );

        return $submission;
    }
}
