<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaAnswer extends Model
{
    use HasTimestamps;

    public $table = 'meta_answers';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function metaPrompt(): BelongsTo
    {
        return $this->belongsTo(MetaPrompt::class, 'meta_prompt_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
