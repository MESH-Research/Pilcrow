<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReviewCoordinatorInvited;
use App\Models\User;
use App\Notifications\InviteReviewCoordinator;
use Illuminate\Support\Facades\Notification;

class NotifyReviewCoordinatorAboutInvitation
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\ReviewCoordinatorInvited  $event
     * @return void
     */
    public function handle(ReviewCoordinatorInvited $event)
    {
        $coordinator = User::where('email', $event->submission_invitation->email)->firstOrFail();
        $notification_data = [
            'submission' => [
                'id' => $event->submission_invitation->id,
            ],
            'inviter' => [
                'name' => $event->submission_invitation->createdBy->name,
                'username' => $event->submission_invitation->createdBy->username,
            ],
            'message' => $event->submission_invitation->message,
            'token' => $event->submission_invitation->token,
        ];
        Notification::send($coordinator, new InviteReviewCoordinator($notification_data));
    }
}
