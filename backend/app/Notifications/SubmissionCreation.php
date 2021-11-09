<?php
declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmissionCreation extends Notification implements ShouldQueue
{
    use Queueable;

    private $creationData;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($creationData)
    {
        $this->after_commit = true;
        $this->creationData = $creationData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
                    ->line($this->creationData['body'])
                    ->action($this->creationData['action'], $this->creationData['url']);
    }

    /**
     * Get the array representation of the notification for the client.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'submission_id' => $this->creationData['submission_id'],
        ];
    }

    /**
     * Get the array representation of the notification for the datatabase.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'submission_id' => $this->creationData['submission_id'],
        ];
    }
}
