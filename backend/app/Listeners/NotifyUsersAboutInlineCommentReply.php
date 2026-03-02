<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\InlineCommentReplyAdded as EventsInlineCommentReplyAdded;
use App\Notifications\InlineCommentReplyAdded;
use Illuminate\Support\Facades\Notification;

class NotifyUsersAboutInlineCommentReply
{
    /**
     * Handle the event.
     *
     * @param \App\Events\InlineCommentReplyAdded $event
     * @return void
     */
    public function handle(EventsInlineCommentReplyAdded $event): void
    {
        $submission = $event->inline_comment->submission;
        $submitters = $submission->submitters()->get();
        $parent_commenter = $event->inline_comment->parent->createdBy()->get();
        $commenters = $event->inline_comment->parent->getCommenters();
        $review_coordinators = $submission->reviewCoordinators()->get();
        $notification_data = [
            'submission' => [
                'id' => $submission->id,
                'title' => $submission->title,
            ],
            'commenter' => [
                'display_label' => $event->inline_comment->createdBy->displayLabel,
            ],
            'type' => 'submission.inline_comment_reply.added',
        ];
        $recipients = $submitters
            ->merge($commenters)
            ->merge($parent_commenter)
            ->merge($review_coordinators)
            ->unique()
            ->filter(function ($user) use ($event) {
                return $user->id !== $event->inline_comment->createdBy->id;
            });

        Notification::send($recipients, new InlineCommentReplyAdded($notification_data));
    }
}
