<?php
declare(strict_types=1);

namespace App\Auth\Grants;

use App\Auth\Abilities\ScopedAbility;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * A single ability granted to a scoped role, optionally conditioned by a
 * {@see Predicate}.
 *
 * The predicate lives on the grant — the pairing of role and ability — not on
 * the ability itself: the same ability (e.g. submission.update-status) is an
 * absolute grant for a review coordinator but a DRAFT-only grant for a
 * submitter. An absolute grant is simply one with no predicate.
 */
final class Grant
{
    /**
     * @param \App\Auth\Abilities\ScopedAbility $ability
     * @param \App\Auth\Grants\Predicate|null $predicate
     */
    public function __construct(
        public readonly ScopedAbility $ability,
        public readonly ?Predicate $predicate = null,
    ) {
    }

    /**
     * Does this grant permit the requested ability for the entity / user?
     *
     * @param \App\Auth\Abilities\ScopedAbility $ability
     * @param \Illuminate\Database\Eloquent\Model|null $entity
     * @param \App\Models\User $user
     * @return bool
     */
    public function permits(ScopedAbility $ability, ?Model $entity, User $user): bool
    {
        if ($this->ability !== $ability) {
            return false;
        }
        if ($this->predicate === null) {
            return true;
        }

        return $entity !== null && $this->predicate->holds($entity, $user);
    }
}
