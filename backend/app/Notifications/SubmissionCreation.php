<?php
declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SubmissionCreation extends Notification implements ShouldQueue
{
    use Queueable;

    public $afterCommit = true;

    private $creationData;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($creationData)
    {
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
        return ['mail'];
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
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'submission_id' => $this->creationData['id'],
        ];
    }
}
