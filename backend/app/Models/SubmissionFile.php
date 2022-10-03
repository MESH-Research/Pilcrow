<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\SubmissionFileImportStatus;
use App\Exceptions\EmptyContentOnImport;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Pandoc\Pandoc;
use function Illuminate\Events\queueable;

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
        static::created(queueable(function (SubmissionFile $file) {
            //Test seeded files are currently added with a /tmp/ file name.  Until those seeders are updated to handle this, ignore processing them.
            if (preg_match('%^/tmp/%', (string)$file->file_upload) == 1) {
                return;
            }
            //Fetch file from DB to ensure it hasn't already been processed or cancelled
            $file->refresh();
            if ($file->import_status->isNot(SubmissionFileImportStatus::Pending)) {
                return;
            }
            $file->import_status = SubmissionFileImportStatus::Processing();
            $file->save();

            $content = new SubmissionContent();
            try {
                $content->submission_id = $file->submission_id;
                $content->submission_file_id = $file->id;
                $content->data = (new Pandoc())
                    ->inputFile(storage_path('app/' . $file->file_upload))
                    ->to('html')
                    ->run();
                if (empty($content->data)) {
                    throw new EmptyContentOnImport();
                }
                $content->save();
            } catch (\Exception $e) {
                $file->import_status = SubmissionFileImportStatus::Failure;
                $file->error_message = 'Exception: ' . get_class($e);
                $file->save();

                return;
            }
            $file->import_status = SubmissionFileImportStatus::Success();
            $file->content_id = $content->id;

            //Make the new content import the "live" content version.
            $submission = $file->submission;
            $submission->content_id = $content->id;
            $submission->save();
        }));
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
