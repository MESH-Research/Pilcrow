<?php
declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Reply-thread coherence for the standalone comment-create mutations, the flat
 * `createInlineComment` / `createOverallComment` analogue of the nested
 * App\Rules\*CommentIdValidity rules the deprecated god-mutation applies.
 *
 * A comment is either top-level (names neither a parent nor a reply target) or a
 * reply that names both: a parent and the comment it replies to. The reply
 * target must be the parent itself or one of the parent's replies, all within
 * the same submission.
 *
 * The parent / reply-target ids are injected at construction from the Lighthouse
 * {@see \Nuwave\Lighthouse\Validation\Validator}'s input-scoped args (the
 * validators below read them with `$this->arg(...)`). A {@see \Illuminate\Contracts\Validation\DataAwareRule}
 * is deliberately NOT used: under `@spread` Lighthouse runs the rule at the field
 * root with the data nested under the `input` key, so a flat `$data['parent_id']`
 * lookup would silently see null and pass every reply. The rule is attached to
 * `submission_id` (always present) so it runs for every create, top-level or
 * reply.
 *
 * @see \App\GraphQL\Validators\CreateInlineCommentValidator
 * @see \App\GraphQL\Validators\CreateOverallCommentValidator
 */
final class CommentReplyCoherence implements ValidationRule
{
    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $model the comment model the thread lives on
     * @param mixed $parentId the parent_id arg, or null for a top-level comment
     * @param mixed $replyToId the reply_to_id arg, or null for a top-level comment
     */
    public function __construct(
        private readonly string $model,
        private readonly mixed $parentId,
        private readonly mixed $replyToId,
    ) {
    }

    /**
     * Validate the reply thread. Attached to `submission_id`, so $value is the
     * submission id the comment is being created under.
     *
     * @param string $attribute
     * @param mixed $value the submission_id
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $hasParent = $this->parentId !== null;
        $hasReplyTo = $this->replyToId !== null;

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
        $parent = $this->model::withTrashed()->find($this->parentId);
        if ($parent === null || (int)$parent->submission_id !== (int)$value) {
            $fail('Invalid reply.');

            return;
        }

        $replyToId = (int)$this->replyToId;
        $isParent = (int)$parent->id === $replyToId;
        $isReplyOfParent = in_array($replyToId, $parent->replies()->pluck('id')->all(), true);
        if (! $isParent && ! $isReplyOfParent) {
            $fail('Invalid reply.');
        }
    }
}
