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

    protected $casts = [
        'inline_comment_style_criteria' => 'json',
    ];

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
}
