<?php
declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmissionStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var array
     */
    private $updateData;

    /**
     * Create a new notification instance.
     *
     * @param array $updateData
     * @return void
     */
    public function __construct($updateData)
    {
        $this->updateData = $updateData;
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
        return (new MailMessage)
            ->subject($this->updateData['subject'])
            ->line($this->updateData['body'])
            ->action($this->updateData['action'], $this->updateData['url']);
    }

    /**
     * Get the array representation of the notification for the client.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'submission' => [
                'id' => $this->updateData['submission']['id'],
                'title' => $this->updateData['submission']['title'],
                'status' => $this->updateData['submission']['status'],
                'status_name' => $this->updateData['submission']['status_name'],
            ],
            'type' => $this->updateData['type'] ?? 'submission.updated',
            'body' => $this->updateData['body'] ?? '',
            'action' => $this->updateData['action'],
            'url' => $this->updateData['url'],
        ];
    }
}
