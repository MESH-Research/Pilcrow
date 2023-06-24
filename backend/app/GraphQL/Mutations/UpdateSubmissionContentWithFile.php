<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Enums\SubmissionFileImportStatus;
use App\Models\Submission;
use App\Models\SubmissionContent;
use App\Models\SubmissionFile;
use GraphQL\Error\Error;
use Pandoc\Facades\Pandoc;

class UpdateSubmissionContentWithFile
{
    /**
     * Upload a file, use Pandoc to convert its contents to HTML, and store the HTML in the database
     *
     * @param  mixed  $_
     * @param  array<string, mixed>  $args
     * @return \App\Models\Submission
     * @throws GraphQL\Error\Error
     */
    public function update($_, array $args): ?Submission
    {
        // @var \Illuminate\Http\UploadedFile $file_upload
        $file_upload = $args['file_upload'];
        $id = $args['submission_id'];
        $submission = Submission::where('id', $id)->firstOrFail();

        $content = new SubmissionContent();
        $content->submission_id = $id;

        $file = SubmissionFile::create([
            'submission_id' => $args['submission_id'],
            'file_upload' => $file_upload->storePublicly('uploads'),
            'content_id' => $content->id,
        ]);

        try {
            $content->data = Pandoc::
                inputFile(storage_path('app/' . $file->file_upload))
                ->noStandalone()
                ->to('html')
                ->run();
        } catch (\Exception) {
            throw new Error('Unable to convert file');
        }

        $file->import_status = SubmissionFileImportStatus::Success();

        if (!$file->save()) {
            throw new Error('Unable to save file');
        }

        $content->submission_file_id = $file->id;

        if (!$content->save()) {
            throw new Error('Unable to save content');
        }

        $submission->content_id = $content->id;

        if (!$submission->save()) {
            throw new Error('Unable to save submission');
        }

        return $submission;
    }
}
