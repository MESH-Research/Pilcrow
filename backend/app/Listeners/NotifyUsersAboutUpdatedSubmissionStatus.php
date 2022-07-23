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
        $default = [
            'submission' => [
                'id' => $event->submission->id,
                'title' => $event->submission->title,
                'status' => $status,
                'status_name' => $event->submission->getStatusNameAttribute(),
            ],
            'type' => 'submission.updated',
            'url' => url('/submission/' . $event->submission->id),
            'action' => 'View Submission',
            'subject' => 'Submission Status Update',
            'body' => 'The status of a submission has been updated.',
        ];
        $actions = [
            1 => $this->initiallySubmitted($default),
            2 => $this->resubmissionRequested($default),
            3 => $this->resubmitted($default),
            4 => $this->acceptedForReview($default),
            5 => $this->rejected($default),
            6 => $this->acceptedAsFinal($default),
            7 => $this->expired($default),
            8 => $this->under_review($default),
            9 => $this->awaiting_decision($default),
            10 => $this->awaiting_revision($default),
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
        $default['body'] = 'A submission has been submitted to the publication. Its fitness for review is to be determined.';
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
        $default['body'] = 'A submission has been determined to be unfit for review. A request has been made for resubmission.';
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
        $default['body'] = 'A submission has been resubmitted to the publication.';
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
        $default['body'] = 'A submission has been accepted for review. It is currently awaiting review from the assigned reviewers.';
        $default['type'] = 'submission.accepted_for_review';
        return $default;
    }

    /**
     * @param array $default
     * @return array
     */
    public function rejected($default)
    {
        $default['subject'] = 'Submission Rejected for Review';
        $default['body'] = 'A submission has been determined to be unfit for review and has been rejected.';
        $default['type'] = 'submission.rejected_for_review';
        return $default;
    }

    /**
     * @param array $default
     * @return array
     */
    public function acceptedAsFinal($default)
    {
        $default['subject'] = 'Submission Accepted as Final';
        $default['body'] = 'A submission has been reviewed and accepted as final.';
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
        $default['body'] = 'A submission has expired.';
        $default['type'] = 'submission.expired';
        return $default;
    }

    /**
     * @param array $default
     * @return array
     */
    public function under_review($default)
    {
        $default['subject'] = 'Submission Under Review';
        $default['body'] = 'A submission is currently under review.';
        $default['type'] = 'submission.under_review';
        return $default;
    }

    /**
     * @param array $default
     * @return array
     */
    public function awaiting_decision($default)
    {
        $default['subject'] = 'Submission Awaiting Decision';
        $default['body'] = 'A submission has been reviewed and is currently awaiting a decision on whether or not it will be accepted as final.';
        $default['type'] = 'submission.awaiting_decision';
        return $default;
    }

    /**
     * @param array $default
     * @return array
     */
    public function awaiting_revision($default)
    {
        $default['subject'] = 'Submission Awaiting Revision';
        $default['body'] = 'A submission has been requested for revision.';
        $default['type'] = 'submission.awaiting_revision';
        return $default;
    }

    /**
     * @param array $default
     * @return array
     */
    public function archived($default)
    {
        $default['subject'] = 'Submission Archived';
        $default['body'] = 'A submission has been archived.';
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
        $default['body'] = 'A submission has been deleted.';
        $default['type'] = 'submission.deleted';
        return $default;
    }
}
