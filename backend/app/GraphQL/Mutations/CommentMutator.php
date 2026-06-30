<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Exceptions\ClientException;
use App\Models\InlineComment;
use App\Models\OverallComment;
use App\Models\Submission;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * The standalone comment-authoring mutations on a submission: creating a comment
 * (or reply) under review, and editing an existing one.
 *
 * Grouped controller-style (resolved via `Class@method`, the house convention)
 * because comment authoring is one domain concern. Authorization is enforced by
 * the @canFind directive on each field — `review` (held only while reviewable)
 * for the creates, author-ownership (the same gate as delete) for the edits.
 * Reply-thread coherence is enforced declaratively by the per-field @validator
 * on the creates; the edits leave the structural ids untouched, so they need no
 * coherence check. Replies fold into the edits for free: a reply is just a
 * comment row carrying a parent_id, so editing it is editing the row by id.
 *
 * @see \App\GraphQL\Validators\CreateInlineCommentValidator
 * @see \App\GraphQL\Validators\CreateOverallCommentValidator
 */
final class CommentMutator
{
    /**
     * Create an inline comment (or reply) on a submission under review.
     *
     * @param null $_
     * @param array{submission_id: string, content: string, style_criteria?: array, from?: int, to?: int, parent_id?: string, reply_to_id?: string} $args
     * @return \App\Models\Submission
     * @throws \App\Exceptions\ClientException
     */
    public function createInline(null $_, array $args): Submission
    {
        try {
            $submission = Submission::findOrFail($args['submission_id']);
        } catch (ModelNotFoundException $e) {
            throw new ClientException('Not Found', 'createInlineComment', 'SUBMISSION_NOT_FOUND');
        }

        $submission->inlineComments()->create([
            'content' => $args['content'],
            // Default to an empty list, never null: the styleCriteria accessor
            // json_decodes the raw column for a top-level comment.
            'style_criteria' => $args['style_criteria'] ?? [],
            'from' => $args['from'] ?? null,
            'to' => $args['to'] ?? null,
            'parent_id' => $args['parent_id'] ?? null,
            'reply_to_id' => $args['reply_to_id'] ?? null,
        ]);

        return $submission;
    }

    /**
     * Create an overall comment (or reply) on a submission under review.
     *
     * @param null $_
     * @param array{submission_id: string, content: string, parent_id?: string, reply_to_id?: string} $args
     * @return \App\Models\Submission
     * @throws \App\Exceptions\ClientException
     */
    public function createOverall(null $_, array $args): Submission
    {
        try {
            $submission = Submission::findOrFail($args['submission_id']);
        } catch (ModelNotFoundException $e) {
            throw new ClientException('Not Found', 'createOverallComment', 'SUBMISSION_NOT_FOUND');
        }

        $submission->overallComments()->create([
            'content' => $args['content'],
            'parent_id' => $args['parent_id'] ?? null,
            'reply_to_id' => $args['reply_to_id'] ?? null,
        ]);

        return $submission;
    }

    /**
     * Edit an inline comment (or reply). Only the supplied editable fields
     * change — content for any row, and (for a top-level comment) its style
     * criteria and range. Replies fold in: a reply is an inline-comment row, so
     * editing it by id just works.
     *
     * @param null $_
     * @param array{submission_id: string, comment_id: string, content?: string, style_criteria?: array, from?: int, to?: int} $args
     * @return \App\Models\Submission
     * @throws \App\Exceptions\ClientException
     */
    public function updateInline(null $_, array $args): Submission
    {
        try {
            $comment = InlineComment::findOrFail($args['comment_id']);
        } catch (ModelNotFoundException $e) {
            throw new ClientException('Not Found', 'updateInlineComment', 'COMMENT_NOT_FOUND');
        }

        $comment->update(
            array_intersect_key($args, array_flip(['content', 'style_criteria', 'from', 'to']))
        );

        return $comment->submission;
    }

    /**
     * Edit an overall comment (or reply). Only the supplied content changes.
     * Replies fold in: a reply is an overall-comment row, so editing it by id
     * just works.
     *
     * @param null $_
     * @param array{submission_id: string, comment_id: string, content?: string} $args
     * @return \App\Models\Submission
     * @throws \App\Exceptions\ClientException
     */
    public function updateOverall(null $_, array $args): Submission
    {
        try {
            $comment = OverallComment::findOrFail($args['comment_id']);
        } catch (ModelNotFoundException $e) {
            throw new ClientException('Not Found', 'updateOverallComment', 'COMMENT_NOT_FOUND');
        }

        $comment->update(
            array_intersect_key($args, array_flip(['content']))
        );

        return $comment->submission;
    }
}
