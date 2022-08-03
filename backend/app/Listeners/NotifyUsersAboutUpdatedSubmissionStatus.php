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
        $user = $event->submission->updatedBy;
        $publication = $event->submission->publication;
        $default = [
            'submission' => [
                'id' => $event->submission->id,
                'title' => (string)$event->submission->title,
                'status' => $status,
                'status_name' => $event->submission->getStatusNameAttribute(),
            ],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
            ],
            'publication' => [
                'id' => $publication->id,
                'name' => $publication->name,
            ],
            'type' => 'submission.updated',
            'url' => url('/submission/' . $event->submission->id),
            'action' => 'View Submission',
        ];
        $actions = [
            1 => $this->initiallySubmitted($default),
            2 => $this->resubmissionRequested($default),
            3 => $this->resubmitted($default),
            4 => $this->acceptedForReview($default),
            5 => $this->rejected($default),
            6 => $this->acceptedAsFinal($default),
            7 => $this->expired($default),
            8 => $this->underReview($default),
            9 => $this->awaitingDecision($default),
            10 => $this->awaitingRevision($default),
            11 => $this->archived($default),
            12 => $this->deleted($default),
        ];
        $notification_data = $actions[$status];

        // Notify submitters, reviewers, review coordinators, and editors
        Notification::send(
            $event->submission->users,
            new SubmissionStatusUpdated($notification_data)
        );
        Notification::send($event->submission->publication->editors, new SubmissionStatusUpdated($notification_data));
    }

    /**
     * @param array $default
     * @return array
     */
    public function initiallySubmitted($default)
    {
        $default['subject'] = 'Submission Submitted';
        $default['body'] = $default['submission']['title'] . ' has been submitted for review. ' .
                           'Its fitness for review is to be determined.';
        $default['type'] = 'submission.initially_submitted';

        return $default;
    }

    /**
     * @param array $default
     * @return array
     */
    public function resubmissionRequested($default)
    {
        $default['subject'] = 'Resubmission Requested';
        $default['body'] = $default['submission']['title'] . ' has been determined to be unfit for review. ' .
                           'A request has been made for resubmission.';
        $default['type'] = 'submission.resubmission_requested';

        return $default;
    }

    /**
     * @param array $default
     * @return array
     */
    public function resubmitted($default)
    {
        $default['subject'] = 'Submission Resubmitted';
        $default['body'] = $default['submission']['title'] . ' has been resubmitted for review.';
        $default['type'] = 'submission.resubmitted';

        return $default;
    }

    /**
     * @param array $default
     * @return array
     */
    public function acceptedForReview($default)
    {
        $default['subject'] = 'Submission Accepted for Review';
        $default['body'] = $default['submission']['title'] . ' has been accepted for review. ' .
                           'It is currently awaiting review from the assigned reviewers.';
        $default['type'] = 'submission.awaiting_review';

        return $default;
    }

    /**
     * @param array $default
     * @return array
     */
    public function rejected($default)
    {
        $default['subject'] = 'Submission Rejected for Review';
        $default['body'] = $default['submission']['title'] . ' has been determined to be unfit ' .
                           'for review and has been rejected.';
        $default['type'] = 'submission.rejected';

        return $default;
    }

    /**
     * @param array $default
     * @return array
     */
    public function acceptedAsFinal($default)
    {
        $default['subject'] = 'Submission Accepted as Final';
        $default['body'] = $default['submission']['title'] . ' has been reviewed and accepted as final.';
        $default['type'] = 'submission.accepted_as_final';

        return $default;
    }

    /**
     * @param array $default
     * @return array
     */
    public function expired($default)
    {
        $default['subject'] = 'Submission Expired';
        $default['body'] = 'The review for ' . $default['submission']['title'] . ' has expired.';
        $default['type'] = 'submission.expired';

        return $default;
    }

    /**
     * @param array $default
     * @return array
     */
    public function underReview($default)
    {
        $default['subject'] = 'Submission Under Review';
        $default['body'] = $default['submission']['title'] . ' is currently under review.';
        $default['type'] = 'submission.under_review';

        return $default;
    }

    /**
     * @param array $default
     * @return array
     */
    public function awaitingDecision($default)
    {
        $default['subject'] = 'Submission Awaiting Decision';
        $default['body'] = $default['submission']['title'] . ' has been reviewed and is awaiting a decision ' .
                           'on whether or not it will be accepted as final.';
        $default['type'] = 'submission.awaiting_decision';

        return $default;
    }

    /**
     * @param array $default
     * @return array
     */
    public function awaitingRevision($default)
    {
        $default['subject'] = 'Submission Awaiting Revision';
        $default['body'] = $default['submission']['title'] . ' has been requested for revision.';
        $default['type'] = 'submission.revision_requested';

        return $default;
    }

    /**
     * @param array $default
     * @return array
     */
    public function archived($default)
    {
        $default['subject'] = 'Submission Archived';
        $default['body'] = $default['submission']['title'] . ' has been archived.';
        $default['type'] = 'submission.archived';

        return $default;
    }

    /**
     * @param array $default
     * @return array
     */
    public function deleted($default)
    {
        $default['subject'] = 'Submission Deleted';
        $default['body'] = $default['submission']['title'] . ' has been deleted.';
        $default['type'] = 'submission.deleted';

        return $default;
    }
}
