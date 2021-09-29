<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\SubmissionFile;

class CreateSubmissionFile
{
    /**
     * Upload a file, store it on the server, and return the newly created record's data.
     *
     * @param  mixed  $_
     * @param  array<string, mixed>  $args
     * @return \App\Models\SubmissionFile|null
     */
    public function __invoke($_, array $args): ?SubmissionFile
    {
        // @var \Illuminate\Http\UploadedFile $file_upload
        $file_upload = $args['file_upload'];

        return SubmissionFile::create([
            'submission_id' => $args['submission_id'],
            'file_upload' => $file_upload->storePublicly('uploads'),
        ]);
    }
}
