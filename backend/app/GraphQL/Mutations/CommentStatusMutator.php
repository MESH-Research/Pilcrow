<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Submission;

final readonly class CommentStatusMutator
{
    /**
     * Validate supplied arguments and return the comments to be marked as read.
     *
     * @param string $type
     * @param string $submission_id
     * @param array{int} $comment_ids
     * @return \App\GraphQL\Mutations\Collection<\App\GraphQL\Mutations\InlineComment|\App\GraphQL\Mutations\OverallComment>
     */
    private function validateArgs($type, $submission_id, $comment_ids)
    {
        if (!$submission_id) {
            throw new \Exception('Submission ID required');
        }
        if (empty($comment_ids)) {
            throw new \Exception('Comment ID(s) required');
        }
        if ($type === 'inline') {
            $comments = Submission::find($submission_id)->inlineCommentsWithReplies;
        } else {
            $comments = Submission::find($submission_id)->overallCommentsWithReplies;
        }
        $matchingComments = $comments->whereIn('id', $comment_ids);
        if ($matchingComments->isEmpty()) {
            throw new \Exception('Invalid comment ID');
        }

        return $matchingComments;
    }

    /**
     * @param null $_
     * @param array{} $args
     * @return \App\GraphQL\Mutations\Collection<\App\GraphQL\Mutations\InlineComment>
     */
    public function inlineRead(null $_, array $args)
    {
        $comments = $this->validateArgs('inline', $args['input']['submission_id'], $args['input']['comment_ids']);
        $comments->map(function ($comment) {
            $comment->markRead();
        });
        return $comments;
    }

    /**
     * @param null $_
     * @param array{} $args
     * @return \App\GraphQL\Mutations\Collection<\App\GraphQL\Mutations\OverallComment>
     */
    public function overallRead(null $_, array $args)
    {
        $comments = $this->validateArgs('overall', $args['input']['submission_id'], $args['input']['comment_ids']);
        $comments->map(function ($comment) {
            $comment->markRead();
        });
        return $comments;
    }
}
