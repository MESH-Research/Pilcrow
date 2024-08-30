<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\InlineCommentAdded as EventsInlineCommentAdded;
use App\Notifications\InlineCommentAdded;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class NotifyUsersAboutNewInlineComment extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Handle the event.
     *
     * @param \App\Events\EventsInlineCommentAdded $event
     * @return void
     */
    public function handle(EventsInlineCommentAdded $event): void
    {
        $submission = $event->inline_comment->submission;
        $submitters = $submission->submitters;
        $review_coordinators = $submission->review_coordinators;
        $notification_data = [
            'submission' => [
                'id' => $submission->id,
                'title' => $submission->title,
            ],
            'commentor' => [
                'display_label' => $event->inline_comment->createdBy->displayLabel,
            ],
            'type' => 'submission.inline_comment.added',
        ];

        Notification::send($submitters, new InlineCommentAdded($notification_data));
        Notification::send($review_coordinators, new InlineCommentAdded($notification_data));
    }
}
