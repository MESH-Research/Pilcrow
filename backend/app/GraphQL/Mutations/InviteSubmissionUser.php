<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Invitation;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\InviteReviewCoordinator;
use App\Notifications\InviteReviewer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

final class InviteSubmissionUser
{
    /**
     * Undocumented function
     *
     * @param  null  $_
     * @param  array{}  $args
     * @return \App\Models\Submission
     */
    public function acceptInvite($_, $args)
    {
        $invite = Invitation::where('token', $args['token'])->firstOrFail();
        $invite->registered_at = Carbon::now()->toDateTimeString();
        $invite->save();
        $user = User::where('email', $invite->email)->firstOrFail();
        $user->staged = null;
        $user->save();
        return Submission::where('id', $invite->submission_id)->firstOrFail();
    }

    /**
     * Create a staged user, attach them as a reviewer to a submisison,
     * and send them an email notification inviting them to accept the assignment
     *
     * @param  null  $_
     * @param  array{}  $args
     * @return \App\Models\Submission
     */
    public function inviteReviewer($_, array $args)
    {
        $submission = Submission::where('id', $args['submission_id'])->firstOrFail();
        $reviewer = $submission->stageReviewer($args['email']);
        $invite = Invitation::create([
            'submission_id' => $args['submission_id'],
            'email' => $reviewer->email,
        ]);
        $auth_user = Auth::user();
        $notification_data = [
            'submission' => [
                'id' => $submission->id,
            ],
            'inviter' => [
                'name' => $auth_user->name,
                'username' => $auth_user->username,
            ],
            'message' => $args['message'],
            'token' => $invite->token,
        ];
        Notification::send($reviewer, new InviteReviewer($notification_data));

        return $submission;
    }

    /**
     * Create a staged user and attach them as a review coordinator to a submisison
     *
     * @param  null  $_
     * @param  array{}  $args
     * @return \App\Models\Submission
     */
    public function inviteReviewCoordinator($_, array $args)
    {
        $submission = Submission::where('id', $args['submission_id'])->firstOrFail();
        $review_coordinator = $submission->stageReviewCoordinator($args['email']);
        $auth_user = Auth::user();
        $notification_data = [
            'submission' => [
                'id' => $submission->id,
            ],
            'inviter' => [
                'name' => $auth_user->name,
                'username' => $auth_user->username,
            ],
            'message' => $args['message'],
        ];
        Notification::send(
            $review_coordinator,
            new InviteReviewCoordinator($notification_data)
        );

        return $submission;
    }
}