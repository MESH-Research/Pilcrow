<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\SubmissionFileImportStatus;
use App\Jobs\ImportFileContent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Event;
use OwenIt\Auditing\Events\AuditCustom;

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
        'content_id',
        'file_upload',
    ];

    protected $casts = [
        'import_status' => SubmissionFileImportStatus::class,
    ];

    /**
     * Undocumented function
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function (SubmissionFile $file) {
            $submission = $file->submission;
            $submission->auditEvent = 'contentUpload';
            $submission->isCustomEvent = true;
            $submission->auditCustomNew = [
                'submission_file_id' => $file->id,
            ];

            Event::dispatch(AuditCustom::class, [$submission]);
        });

        static::created(function (SubmissionFile $file) {
            //Test files start with /tmp so skip them for now.
            $fileName = (string)$file->file_upload;
            $user = auth()->user();
            ImportFileContent::dispatchIf(preg_match('%^/tmp/%', $fileName) == 0, $file, $user);
        });
    }

    /**
     * Submission that belongs to the submission file
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    /**
     * Content that belongs to the submission file
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(SubmissionContent::class, 'content_id');
    }
}
