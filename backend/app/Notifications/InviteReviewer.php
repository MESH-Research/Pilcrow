<?php
declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InviteReviewer extends Notification implements ShouldQueue
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
     * Email inviting a staged user as a reviewer to a submission
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        $mail = new MailMessage();
        $name = ($this->data['inviter']['name'] ?: $this->data['inviter']['username']);
        $mail->subject('Invitation to Review')
            ->line('You have been invited by ' . $name . ' to review a submission.')
            ->linesIf($this->data['message'], [
                'Comment from ' . $name . ': ',
                $this->data['message'],
            ])
            ->action('Accept Invitation', url('/accept-invite/' . $this->data['token']));

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
