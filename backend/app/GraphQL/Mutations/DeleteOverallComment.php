<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Exceptions\ClientException;
use App\Models\OverallComment;
use App\Models\Submission;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class DeleteOverallComment
{
    /**
     * Soft delete an overall comment of a submission
     *
     * @param null $_
     * @param  array{id: string}  $args
     * @return \App\Models\Submission
     * @throws \App\Exceptions\ClientException
     */
    public function delete(null $_, array $args): Submission
    {
        try {
            $overall_comment = OverallComment::findOrFail($args['comment_id']);
        } catch (ModelNotFoundException $e) {
            throw new ClientException('Not Found', 'deleteOverallComment', 'COMMENT_NOT_FOUND');
        }
        try {
            $overall_comment->delete();

            return $overall_comment->submission;
        } catch (\Exception $e) {
            throw new ClientException('Error', 'deleteOverallComment', 'UNABLE_TO_DELETE_COMMENT');
        }
    }
}
