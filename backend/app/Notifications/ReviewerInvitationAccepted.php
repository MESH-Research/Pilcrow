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
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mail = new MailMessage();
        $invitee_name = ($this->data['invitee']['name'] ?: $this->data['invitee']['username']);
        $inviter_name = ($this->data['inviter']['name'] ?: $this->data['inviter']['username']);
        $mail->subject('A Reviewer Has Accepted an Invitation')
            ->line($invitee_name . ' has accepted the invitation from ' . $inviter_name . ' to review a submission.')
            ->action('Visit Submission', url('/submission/' . $this->data['submission']['id']));

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
                'name' => $this->data['inviter']['name'],
                'username' => $this->data['inviter']['username'],
            ],
            'invitee' => [
                'name' => $this->data['invitee']['name'],
                'username' => $this->data['invitee']['username'],
            ],
            'type' => 'submission.invitation.accepted',
        ];
    }
}
