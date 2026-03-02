<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\OverallComment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OverallCommentAdded
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @var \App\Models\OverallComment $overall_comment
     */
    public $overall_comment;

    /**
     * @param \App\Models\OverallComment $overall_comment
     */
    public function __construct(OverallComment $overall_comment)
    {
        $this->overall_comment = $overall_comment;
    }
}
