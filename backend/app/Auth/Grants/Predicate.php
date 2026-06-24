<?php
declare(strict_types=1);

namespace App\Auth\Grants;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * An attribute condition attached to a {@see Grant}. A conditional grant is
 * allowed only when its predicate holds for the (entity, user) pair — e.g. the
 * submission is a draft, or the user owns the comment.
 *
 * Predicates are small, reusable, unit-testable value objects rather than
 * inline closures so the same condition (SubmissionIsDraft, IsOwner, ...) can be shared
 * across grants and asserted in isolation.
 */
interface Predicate
{
    /**
     * Does this condition hold for the given entity and acting user?
     *
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Models\User $user
     * @return bool
     */
    public function holds(Model $entity, User $user): bool;
}
