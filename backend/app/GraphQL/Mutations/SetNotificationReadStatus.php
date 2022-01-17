<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class SetNotificationReadStatus
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

        $user->notifications(function($notification) use ($args) {

        })

        /** @var Notification $notification */
        $notification = Notification::where('id', $args['id'])->first();

        return $notification;
    }
}
