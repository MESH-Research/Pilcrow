<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Submission;
use App\Models\SubmissionContent;
use GraphQL\Error\Error;

final class UpdateSubmissionContent
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     * @return \App\Models\Submission
     */
    public function __invoke($_, array $args)
    {
        $id = $args['id'];

        $submission = Submission::find($id);

        if (!$submission) {
            throw new Error('Submission not found');
        }

        $content = new SubmissionContent();
        $content->data = $args['content'];
        $content->submission_id = $id;

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
