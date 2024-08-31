<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\OverallCommentAdded as EventsOverallCommentAdded;
use App\Notifications\OverallCommentAdded;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class NotifyUsersAboutOverallComment extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Handle the event.
     *
     * @param \App\Events\EventsOverallCommentAdded $event
     * @return void
     */
    public function handle(EventsOverallCommentAdded $event): void
    {
        $submission = $event->overall_comment->submission;
        $submitters = $submission->submitters;
        $review_coordinators = $submission->review_coordinators;
        $notification_data = [
            'submission' => [
                'id' => $submission->id,
                'title' => $submission->title,
            ],
            'commentor' => [
                'display_label' => $event->overall_comment->createdBy->displayLabel,
            ],
            'type' => 'submission.overall_comment.added',
        ];

        Notification::send($submitters, new OverallCommentAdded($notification_data));
        Notification::send($review_coordinators, new OverallCommentAdded($notification_data));
    }
}
