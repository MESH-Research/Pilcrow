<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    protected $casts = [
        'options' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function buildSortQuery(): Builder
    {
        return static::query()->where('meta_form_id', $this->meta_form_id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function metaForm(): BelongsTo
    {
        return $this->belongsTo(MetaForm::class, 'meta_form_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function responses(): HasMany
    {
        return $this->hasMany(SubmissionMetaResponse::class, 'meta_prompt_id');
    }
}
