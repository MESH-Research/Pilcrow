<?php
declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewerInvitationAccepted extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var array
     */
    private $data;

    /**
     * Create a new notification instance.
     *
     * @param array $notification_data
     * @return void
     */
    public function __construct($notification_data)
    {
        $this->data = $notification_data;
        $this->after_commit = true;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        return [
            'database',
            'mail',
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        $mail = new MailMessage();
        $mail->subject('A Reviewer Has Accepted an Invitation')
            ->line($this->data['invitee']['display_label'] . ' has accepted the invitation from '
                . $this->data['inviter']['display_label'] . ' to review the submission, '
                . $this->data['submission']['title'] . '.')
            ->action('Visit Submission', url('/submission/' . $this->data['submission']['id'] . '/details'));

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'submission' => [
                'id' => $this->data['submission']['id'],
                'title' => $this->data['submission']['title'],
            ],
            'inviter' => [
                'display_label' => $this->data['inviter']['display_label'],
            ],
            'invitee' => [
                'display_label' => $this->data['invitee']['display_label'],
            ],
            'type' => 'submission.invitation.reviewer.accepted',
        ];
    }
}
