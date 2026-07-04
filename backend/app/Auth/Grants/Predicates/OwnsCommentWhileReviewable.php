<?php
declare(strict_types=1);

namespace App\Auth\Grants\Predicates;

use App\Auth\Grants\Predicate;
use App\Models\Contracts\Comment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Holds when the acting user authored the comment AND its submission is still
 * reviewable. Backs the conditional comment.update / comment.delete grants on
 * the comment-capable roles: an author may revise or retract their own words
 * while review is open, and only then — once the submission leaves the
 * reviewable window the comment is part of the settled record.
 *
 * Authorship is the comment's own attribute (`created_by`); the reviewable
 * window is a property of its owning submission. Editorial moderation by other
 * roles (a coordinator / editor acting on someone else's comment) is a separate,
 * future grant without the authorship clause — it is deliberately NOT folded in
 * here.
 */
final class OwnsCommentWhileReviewable implements Predicate
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Models\User $user
     * @return bool
     */
    public function holds(Model $entity, User $user): bool
    {
        if (! $entity instanceof Comment) {
            return false;
        }

        if ($entity->created_by !== $user->id) {
            return false;
        }

        $submission = $entity->submission;

        return $submission instanceof Submission && $submission->isReviewable();
    }
}
