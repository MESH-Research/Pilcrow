<?php
declare(strict_types=1);

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InlineComment extends BaseModel
{
    use HasFactory;
    use CreatedUpdatedBy;

    /**
     * The submission that owns the inline comment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * The replies to an inline comment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies(): HasMany
    {
        return $this->hasMany(InlineComment::class, 'parent_id');
    }

    /**
     * The associated style criteria of an inline comment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function style_criteria(): HasMany
    {
        return $this->hasMany(StyleCriteria::class, 'id');
    }
}
