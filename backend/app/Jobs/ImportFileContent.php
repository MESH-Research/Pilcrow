<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Enums\SubmissionFileImportStatus;
use App\Exceptions\EmptyContentOnImport;
use App\Models\SubmissionContent;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Pandoc\Facades\Pandoc;

class ImportFileContent implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The file to be processed
     *
     * @var \App\Models\SubmissionFile
     */
    public $file;

    /**
     * The user that triggered the import
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\SubmissionFile $file File to import content from.
     * @param \App\Models\User $user User who triggered the import
     * @return void
     */
    public function __construct(SubmissionFile $file, User $user)
    {
        $this->file = $file;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //Fetch file from DB to ensure it hasn't already been processed or cancelled
        $this->file->refresh();
        if ($this->file->import_status->isNot(SubmissionFileImportStatus::Pending)) {
            return;
        }
        $this->file->import_status = SubmissionFileImportStatus::Processing();
        $this->file->save();

        $content = new SubmissionContent();
        try {
            $content->submission_id = $this->file->submission_id;
            $content->submission_file_id = $this->file->id;
            $content->data = Pandoc::
                inputFile(storage_path('app/' . $this->file->file_upload))
                ->to('html')
                ->run();
            if (empty($content->data)) {
                throw new EmptyContentOnImport();
            }
            $content->save();
        } catch (\Exception $e) {
            $this->file->import_status = SubmissionFileImportStatus::Failure;
            $this->file->error_message = 'Exception: ' . get_class($e) . ' (' . $e->getMessage() . ')';
            $this->file->save();

            return;
        }
        $this->file->import_status = SubmissionFileImportStatus::Success();
        $this->file->content_id = $content->id;
        $this->file->save();

        //Make the new content import the "live" content version.
        $submission = $this->file->submission;
        $submission->content_id = $content->id;
        $submission->updated_by = $this->user->id;
        $submission->save();
    }
}
