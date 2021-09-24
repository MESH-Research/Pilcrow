<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\SubmissionUser;

class CreateSubmission
{
    /**
     * Create a submission with a user and file upload
     *
     * @param  mixed  $_
     * @param  array<string, mixed>  $args
     * @return \App\Models\Submission
     */
    public function __invoke($_, array $args): Submission
    {
        $submission = Submission::create([
            'title' => $args['title'],
            'publication_id' => $args['publication_id'],
        ]);
        collect($args['users']['connect'])->map(function ($user) use ($submission) {
            SubmissionUser::create([
                'user_id' => $user['id'],
                'submission_id' => $submission->id,
                'role_id' => $user['role_id'],
            ]);
        });
        collect($args['files']['create'])->map(function ($file) use ($submission) {
            return SubmissionFile::create([
                'submission_id' => $submission->id,
                'file_upload' => $file->storePublicly('uploads'),
            ]);
        });

        return $submission;
    }
}
