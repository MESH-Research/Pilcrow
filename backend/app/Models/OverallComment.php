<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasUserAuditFields;
use App\Models\Traits\ReadStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OverallComment extends BaseModel
{
    use HasFactory;
    use HasUserAuditFields;
    use SoftDeletes;
    use ReadStatus;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'submission_id',
        'content',
        'reply_to_id',
        'parent_id',
    ];

    /**
     * The submission that owns the overall comment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * The replies to an overall comment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies(): HasMany
    {
        $thread_replies = $this->hasMany(OverallComment::class, 'parent_id');
        if ($thread_replies->count() > 0) {
            return $thread_replies;
        } else {
            return $this->hasMany(OverallComment::class, 'reply_to_id');
        }
    }

    /**
     * The creator of the overall comment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The updater of the overall comment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function username(): Attribute
    {
        return Attribute::make(
            get: fn(int $value) => $this->trashed() ? '' : $value,
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function content(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->trashed() ? 'This comment has been deleted' : $value,
        );
    }
}
