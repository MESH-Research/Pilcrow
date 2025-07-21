<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class MetaPromptSet extends Model implements Sortable
{
    use SortableTrait;
    use SoftDeletes;
    use HasFactory;

    public $table = 'meta_prompt_sets';

    protected $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    public function buildSortQuery(): Builder
    {
        return static::query()->where('publication_id', $this->publication_id);
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function metaPrompts(): HasMany
    {
        return $this->hasMany(MetaPrompt::class, 'meta_prompt_set_id');
    }
}
