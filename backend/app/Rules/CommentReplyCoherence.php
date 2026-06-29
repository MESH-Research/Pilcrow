<?php
declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Reply-thread coherence for the standalone comment-create mutations, the flat
 * `createInlineComment` / `createOverallComment` analogue of the nested
 * App\Rules\*CommentIdValidity rules the deprecated god-mutation applies.
 *
 * A comment is either top-level (names neither a parent nor a reply target) or a
 * reply that names both: a parent and the comment it replies to. The reply
 * target must be the parent itself or one of the parent's replies, all within
 * the same submission. Reads the thread ids from the flat resolver args via
 * {@see DataAwareRule}, so attaching it to a single attribute is enough.
 *
 * @see \App\GraphQL\Validators\CreateInlineCommentValidator
 * @see \App\GraphQL\Validators\CreateOverallCommentValidator
 */
final class CommentReplyCoherence implements ValidationRule, DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    private array $data = [];

    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $model the comment model the thread lives on
     */
    public function __construct(
        private readonly string $model,
    ) {
    }

    /**
     * Set the data under validation.
     *
     * @param array<string, mixed> $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Validate the reply thread named by the flat args.
     *
     * @param string $attribute
     * @param mixed $value
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $parentId = $this->data['parent_id'] ?? null;
        $replyToId = $this->data['reply_to_id'] ?? null;
        $submissionId = $this->data['submission_id'] ?? null;

        $hasParent = $parentId !== null;
        $hasReplyTo = $replyToId !== null;

        // A top-level comment names neither.
        if (! $hasParent && ! $hasReplyTo) {
            return;
        }

        // A reply must name both.
        if ($hasParent !== $hasReplyTo) {
            $fail('Invalid reply.');

            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model|null $parent */
        $parent = $this->model::withTrashed()->find($parentId);
        if ($parent === null || (int)$parent->submission_id !== (int)$submissionId) {
            $fail('Invalid reply.');

            return;
        }

        $replyToId = (int)$replyToId;
        $isParent = (int)$parent->id === $replyToId;
        $isReplyOfParent = in_array($replyToId, $parent->replies()->pluck('id')->all(), true);
        if (! $isParent && ! $isReplyOfParent) {
            $fail('Invalid reply.');
        }
    }
}
