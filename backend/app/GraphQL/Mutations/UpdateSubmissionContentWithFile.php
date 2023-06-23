<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Enums\SubmissionFileImportStatus;
use App\Models\Submission;
use App\Models\SubmissionContent;
use App\Models\SubmissionFile;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Event;

class UpdateSubmissionContentWithFile
{
    /**
     * Upload a file, store it on the server, and return the newly created record's data.
     *
     * @param  mixed  $_
     * @param  array<string, mixed>  $args
     * @return \App\Models\Submission
     * @throws GraphQL\Error\Error
     */
    public function __invoke($_, array $args): ?Submission
    {
        // @var \Illuminate\Http\UploadedFile $file_upload
        $file_upload = $args['file_upload'];
        $id = $args['submission_id'];
        $submission = Submission::where('id', $id)->firstOrFail();

        $content = new SubmissionContent();
        $content->submission_id = $id;

        $file = SubmissionFile::create([
            'submission_id' => $args['submission_id'],
            'file_upload' => $file_upload->storePublicly('app/uploads'),
            'content_id' => $content->id,
        ]);

        try {
            $content->data = (new \Pandoc\Pandoc)
                ->from('markdown')
                ->input('# Hello World')
                ->to('html')
                ->run();
        } catch (Error $e) {
            print_r("Hello Error");
            throw new Error($e->getMessage());
        }

        $file->import_status = SubmissionFileImportStatus::Success();

        if (!$file->save()) {
            throw new Error('Unable to save file');
        }

        $content->submission_file_id = $file->id;

        if (!$content->save()) {
            throw new Error('Unable to save content');
        }

        // Update Submission
        $submission = $file->submission;
        $submission->auditEvent = 'contentUpload';
        $submission->isCustomEvent = true;
        $submission->auditCustomNew = [
            'submission_file_id' => $file->id,
        ];
        Event::dispatch(AuditCustom::class, [$submission]);
        $submission->content_id = $content->id;

        if (!$submission->save()) {
            throw new Error('Unable to save submission');
        }

        return $submission;
    }
}
