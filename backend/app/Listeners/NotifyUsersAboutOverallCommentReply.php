<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\OverallCommentReplyAdded as EventsOverallCommentReplyAdded;
use App\Notifications\OverallCommentReplyAdded;
use Illuminate\Support\Facades\Notification;

class NotifyUsersAboutOverallCommentReply
{
    /**
     * Handle the event.
     *
     * @param \App\Events\OverallCommentReplyAdded $event
     * @return void
     */
    public function handle(EventsOverallCommentReplyAdded $event): void
    {
        $submission = $event->overall_comment->submission;
        $submitters = $submission->submitters()->get();
        $parent_commenter = $event->overall_comment->parent->createdBy()->get();
        $commenters = $event->overall_comment->parent->getCommenters();
        $review_coordinators = $submission->reviewCoordinators()->get();
        $notification_data = [
            'submission' => [
                'id' => $submission->id,
                'title' => $submission->title,
            ],
            'commenter' => [
                'display_label' => $event->overall_comment->createdBy->displayLabel,
            ],
            'type' => 'submission.overall_comment_reply.added',
        ];
        $recipients = $submitters
            ->merge($commenters)
            ->merge($parent_commenter)
            ->merge($review_coordinators)
            ->unique()
            ->filter(function ($user) use ($event) {
                return $user->id !== $event->overall_comment->createdBy->id;
            });

        Notification::send($recipients, new OverallCommentReplyAdded($notification_data));
    }
}
