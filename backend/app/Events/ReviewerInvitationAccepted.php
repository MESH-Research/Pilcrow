<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\SubmissionInvitation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReviewerInvitationAccepted
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @var \App\Models\SubmissionInvitation $submission_invitation
     */
    public $submission_invitation;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\SubmissionInvitation $submission_invitation
     * @return void
     */
    public function __construct(SubmissionInvitation $submission_invitation)
    {
        $this->submission_invitation = $submission_invitation;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
