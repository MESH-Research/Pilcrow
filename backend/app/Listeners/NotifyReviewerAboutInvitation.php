<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReviewerInvited;
use App\Models\User;
use App\Notifications\InviteReviewer;
use Illuminate\Support\Facades\Notification;

class NotifyReviewerAboutInvitation
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
                'display_label' => $event->submission_invitation->createdBy->displayLabel,
            ],
            'message' => $event->submission_invitation->message,
            'url' => $event->submission_invitation->getInvitationAcceptanceUrl(),
        ];
        Notification::send($reviewer, new InviteReviewer($notification_data));
    }
}
