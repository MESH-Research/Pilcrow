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
     * @return Collection<InlineComment|OverallComment>
     */
    private function validateArgs($type, $submission_id, $comment_ids)
    {
        if (!$submission_id) {
            throw new \Exception('Submission ID required');
        }
        if (empty($commentIds)) {
            throw new \Exception('Comment ID(s) required');
        }
        if ($type === 'inline') {
            $comments = Submission::find($submission_id)->inlineComments();
        } else {
            $comments = Submission::find($submission_id)->overallComments();
        }
        $comments->map(function ($comment) use ($comment_ids) {
            if (!in_array($comment->id, $comment_ids)) {
                throw new \Exception('Invalid comment ID');
            }
        });
        return $comments;
    }

    /**
     * @param array{} $args
     * @return Collection<InlineComment>
     */
    public function inlineRead(null $_, array $args)
    {
        $comments = $this->validateArgs('inline', $args['submission_id'], $args['comment_ids']);
        return $comments->map(function ($comment) {
            $comment->markRead();
        });
    }

    /**
     * @param array{} $args
     * @return Collection<OverallComment>
     */
    public function overallRead(null $_, array $args)
    {
        $comments = $this->validateArgs('overall', $args['submission_id'], $args['comment_ids']);
        return $comments->map(function ($comment) {
            $comment->markRead();
        });
    }

    public function markRead($commentType, $commentIds)
    {
        //TODO: Create new CommentStatus records for each comment id (if they don't already exist)
        //TODO: Return the number of comments statuses created.
    }
}
