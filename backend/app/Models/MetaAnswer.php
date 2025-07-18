<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaAnswer extends Model
{
    use HasTimestamps;

    public $table = 'meta_answers';

    public function metaQuestion(): BelongsTo
    {
        return $this->belongsTo(MetaQuestion::class, 'meta_question_id');
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
