<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Notifications\SubmissionStatusUpdated;
use Illuminate\Support\Facades\Notification;

class NotifyUsersAboutUpdatedSubmissionStatus
{

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $status = $event->submission->status;
        $action = 'View Submission';
        $body = 'The status has been changed';

        $notification_data = [
            'submission' => [
                'id' => $event->submission->id,
                'title' => $event->submission->title,
                'status' => $status,
            ],
            'type' => 'submission.updated',
            'action' => $action,
            'url' => url('/submission/' . $event->submission->id),
            'body' => $body,
        ];
        // Notify submitters, reviewers, review coordinators, and editors
        Notification::send(
            $event->submission->users,
            new SubmissionStatusUpdated($notification_data)
        );
        Notification::send($event->submission->publication->editors, new SubmissionStatusUpdated($notification_data));
    }
}
