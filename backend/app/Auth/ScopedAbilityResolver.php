<?php
declare(strict_types=1);

namespace App\Auth;

use App\Auth\Abilities\ScopedAbility;
use App\Auth\Roles\ScopedRole;
use App\Models\Publication;
use App\Models\PublicationAssignment;
use App\Models\Submission;
use App\Models\SubmissionAssignment;
use App\Models\User;

/**
 * Resolves SCOPED (publication / submission) permissions from the code-owned
 * role -> ability definitions on {@see ScopedRole}.
 *
 * This resolver only gets involved when a publication or submission is in play.
 * Global, application-wide abilities ({@see GlobalAbility}) are NOT its concern —
 * those are checked directly against Bouncer with `$user->can(...)` at the call
 * site; they never pass through here, so Bouncer can never short-circuit a
 * scoped decision.
 *
 * Scoping — who holds which role on which entity — lives in the
 * publication_user / submission_user pivots. The role -> ability map lives in
 * code (each ScopedRole case returns its grants). This service answers "given
 * this user and this entity, is the scoped ability granted?" by resolving the
 * user's effective role(s) for that entity and asking each whether it grants the
 * ability. No DB round-trip beyond reading the pivots. The one global role,
 * application_admin, short-circuits by ROLE (never a Bouncer ability).
 */
class ScopedAbilityResolver
{
    /**
     * Is the scoped ability granted to the user for the given entity?
     *
     * Only the app-admin ROLE short-circuits (never a Bouncer ability); then the
     * user's effective scoped roles for the entity decide.
     *
     * @param \App\Models\User $user
     * @param \App\Auth\Abilities\ScopedAbility $ability
     * @param \App\Models\Publication|\App\Models\Submission|null $entity
     */
    public function allows(User $user, ScopedAbility $ability, $entity = null): bool
    {
        if ($user->isApplicationAdministrator()) {
            return true;
        }

        foreach ($this->effectiveRoles($user, $entity) as $role) {
            if ($role->allows($ability, $entity, $user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve the user's effective scoped roles for an entity.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Publication|\App\Models\Submission|null $entity
     * @return array<int, \App\Auth\Roles\ScopedRole>
     */
    public function effectiveRoles(User $user, $entity = null): array
    {
        $roles = [];

        if ($entity instanceof Publication) {
            $roles = $this->publicationRoles($user, $entity->id);
        } elseif ($entity instanceof Submission) {
            // Direct submission roles plus admin roles inherited from the
            // parent publication (publication admin / editor). The pivots are
            // unique on (user, entity) and publication vs submission role slugs
            // are disjoint, so this merge can never yield a duplicate role.
            $roles = array_merge(
                $this->submissionRoles($user, $entity->id),
                $this->publicationRoles($user, $entity->publication_id)
            );
        }

        return $roles;
    }

    /**
     * @param \App\Models\User $user
     * @param string|int $publicationId
     * @return array<int, \App\Auth\Roles\ScopedRole>
     */
    private function publicationRoles(User $user, $publicationId): array
    {
        $roleSlugs = PublicationAssignment::query()
            ->where('user_id', $user->id)
            ->where('publication_id', $publicationId)
            ->pluck('role')
            ->all();

        return $this->rolesForSlugs($roleSlugs);
    }

    /**
     * @param \App\Models\User $user
     * @param string|int $submissionId
     * @return array<int, \App\Auth\Roles\ScopedRole>
     */
    private function submissionRoles(User $user, $submissionId): array
    {
        $roleSlugs = SubmissionAssignment::query()
            ->where('user_id', $user->id)
            ->where('submission_id', $submissionId)
            ->pluck('role')
            ->all();

        return $this->rolesForSlugs($roleSlugs);
    }

    /**
     * Map the pivot `role` slugs to ScopedRole cases, skipping any that do not
     * correspond to a known scoped role.
     *
     * @param array<int, string|null> $roleSlugs
     * @return array<int, \App\Auth\Roles\ScopedRole>
     */
    private function rolesForSlugs(array $roleSlugs): array
    {
        $roles = [];
        foreach ($roleSlugs as $slug) {
            $role = $slug === null ? null : ScopedRole::tryFrom((string)$slug);
            if ($role !== null) {
                $roles[] = $role;
            }
        }

        return $roles;
    }
}
