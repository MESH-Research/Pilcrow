<?php
declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InviteReviewCoordinator extends Notification implements ShouldQueue
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
        return ['mail'];
    }

    /**
     * Email inviting a staged user as a review coordinator of a submission
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        $mail = new MailMessage();
        $mail->subject('Invitation to Coordinate a Submission Review')
            ->line('You have been invited to coordinate the review of a submission.')
            ->linesIf($this->data['message'], [
                'Comment from ' . ($this->data['inviter']['name'] ?: $this->data['inviter']['username']) . ': ',
                $this->data['message'],
            ])
            ->action('Accept Invitation', url('/submission/' . $this->data['submission']['id']));

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
                'id' => $this->data['id'],
            ],
            'user_by' => [
                'name' => $this->data['user_by']['name'],
                'username' => $this->data['user_by']['username'],
            ],
            'message' => $this->data['message'],
        ];
    }
}
