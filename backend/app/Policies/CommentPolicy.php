<?php
declare(strict_types=1);

namespace App\Policies;

use App\Auth\Abilities\CommentAbility;
use App\Auth\ScopedAbilityResolver;
use App\Models\Contracts\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

/**
 * Authorize edits to a single comment — inline or overall, top-level or reply.
 * Registered for both {@see \App\Models\InlineComment} and
 * {@see \App\Models\OverallComment} in {@see \App\Providers\AuthServiceProvider}
 * since the rule is identical and a comment carries no roles of its own.
 *
 * CREATION is gated elsewhere, by the submission's `review` ability (only a
 * reviewable submission accepts comments). EDITING is the comment's own concern:
 * resolved as a scoped {@see CommentAbility} against the comment, the author may
 * revise or retract their words while review is open
 * ({@see \App\Auth\Grants\Predicates\OwnsCommentWhileReviewable}), and an
 * application administrator may moderate unconditionally (the resolver's
 * short-circuit). Editorial-role moderation is a future grant.
 */
class CommentPolicy
{
    use HandlesAuthorization;

    /**
     * @param \App\Auth\ScopedAbilityResolver $scoped
     */
    public function __construct(private ScopedAbilityResolver $scoped)
    {
    }

    /**
     * Edit a comment's content (and, for a top-level inline comment, its style
     * criteria and range).
     *
     * @param \App\Models\User $user
     * @param \App\Models\Contracts\Comment $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Comment $comment)
    {
        return $this->scoped->allows($user, CommentAbility::Update, $comment)
            ? true
            : Response::deny('UNAUTHORIZED');
    }

    /**
     * Soft-delete a comment.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Contracts\Comment $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Comment $comment)
    {
        return $this->scoped->allows($user, CommentAbility::Delete, $comment)
            ? true
            : Response::deny('UNAUTHORIZED');
    }
}
