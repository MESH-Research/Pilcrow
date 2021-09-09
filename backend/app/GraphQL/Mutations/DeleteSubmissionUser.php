<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Submission;
use App\Models\SubmissionUser;
use Error;

class DeleteSubmissionUser
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     * @return \App\GraphQL\Mutations\App\Models\Submission
     */
    public function delete($_, array $args): Submission
    {
        try {
            $submission_user = SubmissionUser::where('user_id', $args['user_id'])
                ->where('role_id', $args['role_id'])
                ->where('submission_id', $args['submission_id'])->firstOrFail();
            $submission_user->forceDelete();
            return Submission::where('id', $args['submission_id'])->firstOrFail();
        } catch (Error $error) {
            throw $error;
        }
    }
}
