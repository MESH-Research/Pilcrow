<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class MetaPrompt extends Model implements Sortable
{


    use SortableTrait;
    use HasFactory;

    public $table = 'meta_prompts';

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    public function buildSortQuery()
    {
        return static::query()->where('meta_prompt_set_id', $this->meta_prompt_set_id);
    }


    public function promptSet(): BelongsTo
    {
        return $this->belongsTo(MetaPromptSet::class, 'meta_prompt_set_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(MetaAnswer::class, 'meta_prompt_id');
    }
}
