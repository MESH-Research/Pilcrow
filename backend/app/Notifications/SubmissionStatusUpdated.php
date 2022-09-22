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
        $message = new MailMessage();
        $message->subject($this->updateData['subject'] ?? 'Submission Status Update')
            ->line($this->updateData['body'] ?? 'The status of a submission has been updated.')
            ->linesIf($this->updateData['submission']['status_change_comment'], [
                'Comment from ' . ($this->updateData['user']['name'] ?: $this->updateData['user']['username']) . ': ',
                $this->updateData['submission']['status_change_comment'],
            ]);

        if ($this->updateData['action'] !== '') {
            $message->action($this->updateData['action'], $this->updateData['url']);
        }

        return $message;
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
                'status_change_comment' => $this->updateData['submission']['status_change_comment'],
            ],
            'user' => [
                'id' => $this->updateData['user']['id'],
                'name' => $this->updateData['user']['name'],
                'username' => $this->updateData['user']['username'],
            ],
            'publication' => [
                'id' => $this->updateData['publication']['id'],
                'name' => $this->updateData['publication']['name'],
            ],
            'type' => $this->updateData['type'] ?? 'submission.updated',
            'body' => $this->updateData['body'] ?? '',
            'action' => $this->updateData['action'],
            'url' => $this->updateData['url'],
            'subject' => $this->updateData['subject'],
        ];
    }
}
