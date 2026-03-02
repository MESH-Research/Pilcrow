<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\Submission;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubmissionStatusUpdated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @var \App\Models\Submission $submission
     */
    public $submission;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Submission $submission
     * @return void
     */
    public function __construct(Submission $submission)
    {
        $this->submission = $submission;
    }
}
