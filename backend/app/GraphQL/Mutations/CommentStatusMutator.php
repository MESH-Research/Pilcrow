<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

final readonly class CommentStatusMutator
{
    /** @param  array{}  $args */
    public function inline(null $_, array $args)
    {
        $submissionId = $args['submission_id'];
        $comments = $args['comment_ids'];

        if (!$submissionId || empty($comments)) {
            throw new \Exception('Invalid submission or comment IDs provided');
        }

        //TODO: Validate that all the supplied comment ids are valid and belong to the supplied submission
        //TODO: Call markRead with the comment type and comment ids
    }

    public function overall(null $_, array $args)
    {
        $submissionId = $args['submission_id'];
        $comments = $args['comment_ids'];

        if (!$submissionId || empty($comments)) {
            throw new \Exception('Invalid submission or comment IDs provided');
        }

        //TODO: Validate that all the supplied comment ids are valid and belong to the supplied submission
        //TODO: Call markRead with the comment type and comment ids
    }

    public function markRead($commentType, $commentIds)
    {
        //TODO: Create new CommentStatus records for each comment id (if they don't already exist)
        //TODO: Return the number of comments statuses created.
    }
}
