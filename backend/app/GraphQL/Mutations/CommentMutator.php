<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Exceptions\ClientException;
use App\Models\Submission;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * The standalone comment-create mutations on a submission under review.
 *
 * Grouped controller-style (resolved via `Class@method`, the house convention)
 * because the two creates are one domain concern. Authorization (`review`, held
 * only while the submission is reviewable) is enforced by the @canFind directive
 * on each field; reply-thread coherence is enforced declaratively by the
 * per-field @validator, so each method here just creates the row.
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
}
