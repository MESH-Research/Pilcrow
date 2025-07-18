<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Testing\Fluent\Concerns\Has;

class MetaQuestionSet extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'meta_question_sets';

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function metaQuestions(): HasMany
    {
        return $this->hasMany(MetaQuestion::class, 'meta_question_set_id');
    }
}
