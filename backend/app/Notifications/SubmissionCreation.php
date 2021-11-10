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

    /**
     * @var array
     */
    private $creationData;

    /**
     * Create a new notification instance.
     *
     * @param array $creationData
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
     * @return array
     */
    public function via()
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        return (new MailMessage())
                    ->line($this->creationData['body'])
                    ->action($this->creationData['action'], $this->creationData['url']);
    }

    /**
     * Get the array representation of the notification for the client.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'body' => $this->creationData['body'],
            'action' => $this->creationData['action'],
            'url' => $this->creationData['url'],
            'submission_id' => $this->creationData['submission_id'],
        ];
    }
}
