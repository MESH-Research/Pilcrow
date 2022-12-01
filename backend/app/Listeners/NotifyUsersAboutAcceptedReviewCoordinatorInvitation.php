<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReviewCoordinatorInvitationAccepted as EventsReviewCoordinatorInvitationAccepted;
use App\Models\User;
use App\Notifications\ReviewCoordinatorInvitationAccepted;
use Illuminate\Support\Facades\Notification;

class NotifyUsersAboutAcceptedReviewCoordinatorInvitation
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\ReviewCoordinatorInvitationAccepted  $event
     * @return void
     */
    public function handle(EventsReviewCoordinatorInvitationAccepted $event)
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
            'type' => 'submission.invitation.review_coordinator.accepted',
        ];
        Notification::send($submission_users, new ReviewCoordinatorInvitationAccepted($notification_data));
    }
}
