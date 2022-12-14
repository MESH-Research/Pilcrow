<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReviewerInvitationAccepted as EventsReviewerInvitationAccepted;
use App\Models\User;
use App\Notifications\ReviewerInvitationAccepted;
use Illuminate\Support\Facades\Notification;

class NotifyUsersAboutAcceptedReviewerInvitation
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\ReviewerInvited  $event
     * @return void
     */
    public function handle(EventsReviewerInvitationAccepted $event)
    {
        // Notify all submission users that are not the invitee and not staged
        $submission_users = $event->submission_invitation->submission->users->filter(function ($value) use ($event) {
            return $value->email !== $event->submission_invitation->email && $value->staged !== 1;
        });

        $invitee = User::where('email', $event->submission_invitation->email)->firstOrFail();

        $notification_data = [
            'submission' => [
                'id' => $event->submission_invitation->submission->id,
                'title' => $event->submission_invitation->submission->title,
            ],
            'inviter' => [
                'display_label' => $event->submission_invitation->createdBy->displayLabel,
            ],
            'invitee' => [
                'display_label' => $invitee->displayLabel,
            ],
            'message' => $event->submission_invitation->message,
            'token' => $event->submission_invitation->token,
            'type' => 'submission.invitation.accepted',
        ];
        Notification::send($submission_users, new ReviewerInvitationAccepted($notification_data));
    }
}
