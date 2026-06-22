<?php
declare(strict_types=1);

namespace App\Auth;

use App\Models\Publication;
use App\Models\PublicationAssignment;
use App\Models\Submission;
use App\Models\SubmissionAssignment;
use App\Models\User;

/**
 * Resolves scoped (publication / submission) permissions from the code-owned
 * role -> ability definitions on {@see ScopedRole}.
 *
 * Scoping — who holds which role on which entity — lives in the
 * publication_user / submission_user pivots. The role -> ability map lives in
 * code (each ScopedRole case returns its grants). This service answers "given
 * this user and this entity, is the ability granted?" by resolving the user's
 * effective role(s) for that entity and asking each whether it grants the
 * ability. No DB round-trip beyond reading the pivots.
 *
 * Global, runtime-editable abilities are NOT resolved here — those live in
 * Bouncer and are checked via $user->can(). The one global role,
 * application_admin, is granted everything() and short-circuited below.
 */
class AbilityResolver
{
    /**
     * Is the ability granted to the user for the given entity?
     *
     * @param \App\Models\User $user
     * @param \App\Auth\Ability $ability
     * @param \App\Models\Publication|\App\Models\Submission|null $entity
     */
    public function allows(User $user, Ability $ability, $entity = null): bool
    {
        // application_admin is the global super-role (granted everything);
        // short-circuit rather than enumerate every ability.
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
     * @return array<int, \App\Auth\ScopedRole>
     */
    public function effectiveRoles(User $user, $entity = null): array
    {
        $roles = [];

        if ($entity instanceof Publication) {
            $roles = $this->publicationRoles($user, $entity->id);
        } elseif ($entity instanceof Submission) {
            // Direct submission roles plus admin roles inherited from the
            // parent publication (publication admin / editor).
            $roles = array_merge(
                $this->submissionRoles($user, $entity->id),
                $this->publicationRoles($user, $entity->publication_id)
            );
        }

        // Dedupe by backing value (enum instances are not array_unique-able).
        $unique = [];
        foreach ($roles as $role) {
            $unique[$role->value] = $role;
        }

        return array_values($unique);
    }

    /**
     * @param \App\Models\User $user
     * @param string|int $publicationId
     * @return array<int, \App\Auth\ScopedRole>
     */
    private function publicationRoles(User $user, $publicationId): array
    {
        $roleIds = PublicationAssignment::query()
            ->where('user_id', $user->id)
            ->where('publication_id', $publicationId)
            ->pluck('role_id')
            ->all();

        return $this->rolesForIds($roleIds);
    }

    /**
     * @param \App\Models\User $user
     * @param string|int $submissionId
     * @return array<int, \App\Auth\ScopedRole>
     */
    private function submissionRoles(User $user, $submissionId): array
    {
        $roleIds = SubmissionAssignment::query()
            ->where('user_id', $user->id)
            ->where('submission_id', $submissionId)
            ->pluck('role_id')
            ->all();

        return $this->rolesForIds($roleIds);
    }

    /**
     * Map the pivot role_id integers to ScopedRole cases, skipping any that do
     * not correspond to a known scoped role.
     *
     * @param array<int, int|string> $roleIds
     * @return array<int, \App\Auth\ScopedRole>
     */
    private function rolesForIds(array $roleIds): array
    {
        $roles = [];
        foreach ($roleIds as $id) {
            $role = ScopedRole::tryFrom((int)$id);
            if ($role !== null) {
                $roles[] = $role;
            }
        }

        return $roles;
    }
}
