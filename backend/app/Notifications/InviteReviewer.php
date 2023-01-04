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
        $inviter = $this->data['inviter']['display_label'];
        $mail->subject('Invitation to Review')
            ->line('You have been invited by ' . $inviter . ' to review a submission.')
            ->linesIf($this->data['message'], [
                'Comment from ' . $inviter . ': ',
                $this->data['message'],
            ])
            ->action('Accept Invitation', $this->data['url']);

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
            'inviter' => [
                'display_label' => $this->data['inviter']['display_label'],
            ],
            'message' => $this->data['message'],
        ];
    }
}
