<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Models\Role;
use App\Notifications\SubmissionCreated;
use Illuminate\Support\Facades\Notification;

class EmailUsersAboutCreatedSubmission
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * TODO: Refactor the user data to use the "created_by" property once it's added to submissions
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $submitters = $event->submission->users->filter(function ($user) {
            return $user->pivot->role_id == Role::SUBMITTER_ROLE_ID;
        });
        $notification_data = [
            'submission' => [
                'id' => $event->submission->id,
                'title' => $event->submission->title,
            ],
            'user' => [
                'id' => $submitters->first()->id,
                'username' => $submitters->first()->username,
                'name' => $submitters->first()->name,
            ],
            'publication' => [
                'id' => $event->submission->publication->id,
                'name' => $event->submission->publication->name,
            ],
            'type' => 'submission.created',
            'action' => 'Review Submission',
            'url' => url('/submission/' . $event->submission->id),
            'body' => 'A submission has been created.',
        ];
        // Notify submitters and editors
        Notification::send($submitters, new SubmissionCreated($notification_data));
        $editors = $event->submission->publication->users->filter(function ($user) {
            return $user->pivot->role_id == Role::EDITOR_ROLE_ID;
        });
        Notification::send($editors, new SubmissionCreated($notification_data));
    }
}
