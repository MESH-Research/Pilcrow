<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use Illuminate\Notifications\DatabaseNotification as Notification;
use Illuminate\Support\Facades\Auth;

class MarkNotificationRead
{
    /**
     * Mark a single user notification as read
     *
     * @param  mixed  $_
     * @param  array<string, mixed>  $args
     * @return \Illuminate\Notifications\DatabaseNotification
     */
    public function markAsRead($_, array $args): Notification
    {
        $user = Auth::user();
        $notification = Notification::where('id', $args['id'])
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', "App\Models\User")
            ->firstOrFail();
        $notification->markAsRead();

        return $notification;
    }

    /**
     * Mark all of a user's notifications as read
     *
     * @return int
     */
    public function markAllAsRead(): int
    {
        $user = Auth::user();

        return $user->unreadNotifications->map(function ($notification) {
            $notification->markAsRead();

            return $notification;
        })->count();
    }
}
