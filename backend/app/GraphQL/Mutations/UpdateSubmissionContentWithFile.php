<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Submission;
use App\Models\SubmissionFile;

class UpdateSubmissionContentWithFile
{
    /**
     * Upload a file, and create SubmissionFile and SubmissionContent records
     *
     * @param  mixed  $_
     * @param  array<string, mixed>  $args
     * @return \App\Models\Submission
     * @throws Exception
     */
    public function __invoke($_, array $args): ?Submission
    {
        $submission = Submission::where('id', $args['submission_id'])->firstOrFail();
        // @var \Illuminate\Http\UploadedFile $file_upload
        $file_upload = $args['file_upload'];

        SubmissionFile::create([
            'submission_id' => $args['submission_id'],
            'file_upload' => $file_upload->storePublicly('uploads'),
        ]);

        return $submission;
    }
}
