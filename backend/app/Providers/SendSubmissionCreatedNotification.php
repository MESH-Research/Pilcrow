<?php

namespace App\Providers;

use App\Providers\SubmissionCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSubmissionCreatedNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Providers\SubmissionCreated  $event
     * @return void
     */
    public function handle(SubmissionCreated $event)
    {
        $event->user->sendEmailVerificationNotification();
    }
}
