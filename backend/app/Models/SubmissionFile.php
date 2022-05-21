<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SubmissionFile extends Model
{
    use HasFactory;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'submission_id',
        'file_upload',
    ];

    /**
     * Submission that belongs to the submission file
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    /**
     * Content that belongs to the submission file
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function content(): HasOne
    {
        return $this->hasOne(SubmissionContent::class);
    }
}
