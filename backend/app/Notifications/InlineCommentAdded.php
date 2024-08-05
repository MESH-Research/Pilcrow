<?php
declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NotifyUsersAboutNewlyAddedInlineComments extends Notification implements ShouldQueue
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
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
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
