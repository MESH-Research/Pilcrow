<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class MetaForm extends Model implements Sortable
{
    use SortableTrait;
    use SoftDeletes;
    use HasFactory;

    public $table = 'meta_forms';

    protected $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildSortQuery(): Builder
    {
        return static::query()->where('publication_id', $this->publication_id);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCanUpdate(Builder $query): Builder
    {
        $user = Auth::user();

        return $query->whereHas(
            'publication',
            fn(Builder $query) =>
            $query->whereHas(
                'users',
                fn(Builder $query) =>
                $query->where('user_id', $user->id)
                    ->where('role_id', Role::PUBLICATION_ADMINISTRATOR_ROLE_ID)
            )
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function metaPrompts(): HasMany
    {
        return $this->hasMany(MetaPrompt::class, 'meta_form_id')->ordered();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }
}
