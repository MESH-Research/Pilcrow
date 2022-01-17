<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use Illuminate\Notifications\DatabaseNotification as Notification;
use Illuminate\Support\Facades\Auth;

class MarkNotificationReadStatus
{
    /**
     * Create a submission with a user and file upload
     *
     * @param  mixed  $_
     * @param  array<string, mixed>  $args
     * @return Notification
     */
    public function __invoke($_, array $args): Notification
    {
        $user = Auth::user();
        $notification = Notification::where('id', $args['id'])
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', "App\Models\User")
            ->firstOrFail();
        $notification->markAsRead();
        return $notification;
    }
}
