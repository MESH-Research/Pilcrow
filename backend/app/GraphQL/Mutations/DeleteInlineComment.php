<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Exceptions\ClientException;
use App\Models\InlineComment;
use App\Models\Submission;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class DeleteInlineComment
{
    /**
     * Delete an inline comment of a submission
     * Force delete if it has replies; soft delete otherwise
     *
     * @param null $_
     * @param  array{id: string}  $args
     * @return \App\Models\Submission
     * @throws \App\Exceptions\ClientException
     */
    public function delete(null $_, array $args): Submission
    {
        try {
            $inline_comment = InlineComment::findOrFail($args['comment_id']);
        } catch (ModelNotFoundException $e) {
            throw new ClientException('Not Found', 'deleteInlineComment', 'COMMENT_NOT_FOUND');
        }
        try {
            if ($inline_comment->replies->count() == 0) {
                $inline_comment->forceDelete();
            } else {
                $inline_comment->delete();
            }

            return $inline_comment->submission;
        } catch (\Exception $e) {
            throw new ClientException('Error', 'deleteInlineComment', 'UNABLE_TO_DELETE_COMMENT');
        }
    }
}
