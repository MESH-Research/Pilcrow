<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    use HasFactory;

    public const INITIALLY_SUBMITTED = 1;
    public const AWAITING_RESUBMISSION = 2;
    public const RESUBMITTED = 3;
    public const AWAITING_REVIEW = 4;
    public const REJECTED = 5;
    public const ACCEPTED_AS_FINAL = 6;
    public const EXPIRED = 7;
    public const UNDER_REVIEW = 8;
    public const AWAITING_DECISION = 9;
    public const AWAITING_REVISION = 10;
    public const ARCHIVED = 11;
    public const DELETED = 12;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'publication_id',
        'status',
        'content_id',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'status_name',
    ];

    /**
     * The publication that the submission belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class, 'publication_id');
    }

    /**
     * Users that belong to the submission
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot(['id', 'user_id', 'role_id', 'submission_id']);
    }

    /**
     * File uploads that belong to the submission
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(SubmissionFile::class);
    }

    /**
     * Content that belongs to the submission
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function content(): HasOne
    {
        return $this->hasOne(SubmissionContent::class);
    }

    /**
     * @return string
     */
    public function getStatusNameAttribute()
    {
        $statuses = [
            1 => 'INITIALLY_SUBMITTED',
            2 => 'AWAITING_RESUBMISSION',
            3 => 'RESUBMITTED',
            4 => 'AWAITING_REVIEW',
            5 => 'REJECTED',
            6 => 'ACCEPTED_AS_FINAL',
            7 => 'EXPIRED',
            8 => 'UNDER_REVIEW',
            9 => 'AWAITING_DECISION',
            10 => 'AWAITING_REVISION',
            11 => 'ARCHIVED',
            12 => 'DELETED',
        ];

        return $statuses[(int)$this->status];
    }
}
