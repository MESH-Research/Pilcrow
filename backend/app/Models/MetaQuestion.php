<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class MetaQuestion extends Model implements Sortable
{


    use SortableTrait;
    use HasFactory;

    public $table = 'meta_questions';

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    public function buildSortQuery()
    {
        return static::query()->where('meta_question_set_id', $this->meta_question_set_id);
    }


    public function questionSet(): BelongsTo
    {
        return $this->belongsTo(MetaQuestionSet::class, 'meta_question_set_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(MetaAnswer::class, 'meta_question_id');
    }
}
