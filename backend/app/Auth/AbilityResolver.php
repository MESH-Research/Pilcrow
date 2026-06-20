<?php
declare(strict_types=1);

namespace App\Auth;

use App\Models\Publication;
use App\Models\PublicationAssignment;
use App\Models\Role;
use App\Models\Submission;
use App\Models\SubmissionAssignment;
use App\Models\User;

/**
 * Resolves scoped (publication / submission) permissions from the code-owned
 * role -> ability matrix.
 *
 * Scoping — who holds which role on which entity — lives in the
 * publication_user / submission_user pivots. The role -> ability map lives in
 * code (App\Auth\RoleAbilities). This service answers "given this user and this
 * entity, is the ability granted?" by resolving the user's effective role(s)
 * for that entity and checking whether any of them grants it. No DB round-trip:
 * the matrix is read directly from code, so scoped permission changes ship in
 * code and are live on deploy.
 *
 * Global, runtime-editable abilities are NOT resolved here — those live in
 * Bouncer and are checked via $user->can(). Attribute predicates (state,
 * ownership) stay in the policies; this only resolves the role -> ability
 * dimension.
 */
class AbilityResolver
{
    /**
     * Is the ability granted to the user for the given entity?
     *
     * @param \App\Models\User $user
     * @param string $ability
     * @param \App\Models\Publication|\App\Models\Submission|null $entity
     */
    public function allows(User $user, string $ability, $entity = null): bool
    {
        $roles = $this->effectiveRoles($user, $entity);

        // application_admin is the global super-role (granted everything);
        // short-circuit rather than enumerate every ability.
        if (in_array(Role::SLUG_APPLICATION_ADMIN, $roles, true)) {
            return true;
        }

        // A grant in the matrix is either absolute or carries an attribute
        // predicate evaluated against the entity — RoleAbilities::grants
        // resolves both shapes.
        foreach ($roles as $role) {
            if (RoleAbilities::grants($role, $ability, $entity)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve the user's effective role slugs for an entity.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Publication|\App\Models\Submission|null $entity
     * @return array<int, string>
     */
    public function effectiveRoles(User $user, $entity = null): array
    {
        $slugs = [];

        if ($user->isApplicationAdministrator()) {
            $slugs[] = Role::SLUG_APPLICATION_ADMIN;
        }

        if ($entity instanceof Publication) {
            $slugs = array_merge($slugs, $this->publicationRoleSlugs($user, $entity->id));
        } elseif ($entity instanceof Submission) {
            // Direct submission roles plus admin roles inherited from the
            // parent publication (publication admin / editor).
            $slugs = array_merge(
                $slugs,
                $this->submissionRoleSlugs($user, $entity->id),
                $this->publicationRoleSlugs($user, $entity->publication_id)
            );
        }

        return array_values(array_unique(array_filter($slugs)));
    }

    /**
     * @param \App\Models\User $user
     * @param string|int $publicationId
     * @return array<int, string>
     */
    private function publicationRoleSlugs(User $user, $publicationId): array
    {
        $roleIds = PublicationAssignment::query()
            ->where('user_id', $user->id)
            ->where('publication_id', $publicationId)
            ->pluck('role_id')
            ->all();

        return $this->slugsForIds($roleIds);
    }

    /**
     * @param \App\Models\User $user
     * @param string|int $submissionId
     * @return array<int, string>
     */
    private function submissionRoleSlugs(User $user, $submissionId): array
    {
        $roleIds = SubmissionAssignment::query()
            ->where('user_id', $user->id)
            ->where('submission_id', $submissionId)
            ->pluck('role_id')
            ->all();

        return $this->slugsForIds($roleIds);
    }

    /**
     * Map the pivot role_id integers to the slugs the ability matrix is keyed
     * by. The slug is the auth layer's internal vocabulary; storage still keys
     * on role_id (the slug column migration is a deferred follow-on).
     *
     * @param array<int, int|string> $roleIds
     * @return array<int, string>
     */
    private function slugsForIds(array $roleIds): array
    {
        $slugs = [];
        foreach ($roleIds as $id) {
            $slug = Role::slugForId($id);
            if ($slug !== null) {
                $slugs[] = $slug;
            }
        }

        return $slugs;
    }
}
