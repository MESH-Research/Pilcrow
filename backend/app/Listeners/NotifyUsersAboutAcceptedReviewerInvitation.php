<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReviewerInvited;
use App\Models\User;
use App\Notifications\ReviewerInviteAccepted;
use Illuminate\Support\Facades\Notification;

class NotifyUsersAboutAcceptedReviewerInvitation
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\ReviewerInvited  $event
     * @return void
     */
    public function handle(ReviewerInvited $event)
    {
        $reviewer = User::where('email', $event->submission_invitation->email)->firstOrFail();
        $notification_data = [
            'submission' => [
                'id' => $event->submission_invitation->id,
            ],
            'inviter' => [
                'name' => $event->submission_invitation->createdBy->name,
                'username' => $event->submission_invitation->createdBy->username,
            ],
            'invitee' => [
                'name' => $event->submission_invitation->createdBy->name,
                'username' => $event->submission_invitation->createdBy->username,
            ],
            'message' => $event->submission_invitation->message,
            'token' => $event->submission_invitation->token,
        ];
        Notification::send($reviewer, new ReviewerInviteAccepted($notification_data));
    }
}
