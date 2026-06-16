<?php
declare(strict_types=1);

namespace App\Auth;

use App\Models\Publication;
use App\Models\PublicationAssignment;
use App\Models\Role;
use App\Models\Submission;
use App\Models\SubmissionAssignment;
use App\Models\User;
use Silber\Bouncer\Database\Models as BouncerModels;

/**
 * Bridges scoped role assignment (pivots) to the data-driven ability registry
 * (Bouncer).
 *
 * Scoping — who holds which role on which entity — lives in the
 * publication_user / submission_user pivots. The role -> ability map lives in
 * Bouncer (seeded by AbacSeeder). This service answers "given this user and
 * this entity, is the ability granted?" by resolving the user's effective
 * role(s) for that entity and asking Bouncer whether any of them grants it.
 *
 * Attribute predicates (state, ownership) stay in the policies; this only
 * resolves the role -> ability dimension.
 */
class AbilityResolver
{
    /**
     * Memoized [role slug => set of granted ability names].
     *
     * @var array<string, array<string, bool>>
     */
    private array $abilityCache = [];

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
        // short-circuit rather than expand the wildcard.
        if (in_array(Role::SLUG_APPLICATION_ADMIN, $roles, true)) {
            return true;
        }

        foreach ($roles as $role) {
            if (isset($this->abilitiesFor($role)[$ability])) {
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
        return PublicationAssignment::query()
            ->where('user_id', $user->id)
            ->where('publication_id', $publicationId)
            ->pluck('role')
            ->all();
    }

    /**
     * @param \App\Models\User $user
     * @param string|int $submissionId
     * @return array<int, string>
     */
    private function submissionRoleSlugs(User $user, $submissionId): array
    {
        return SubmissionAssignment::query()
            ->where('user_id', $user->id)
            ->where('submission_id', $submissionId)
            ->pluck('role')
            ->all();
    }

    /**
     * Granted ability names for a role slug, as a name => true lookup.
     *
     * @return array<string, bool>
     */
    private function abilitiesFor(string $slug): array
    {
        if (!isset($this->abilityCache[$slug])) {
            $role = BouncerModels::role()->where('name', $slug)->first();

            $names = $role
                ? $role->getAbilities()->pluck('name')->all()
                : [];

            $this->abilityCache[$slug] = array_fill_keys($names, true);
        }

        return $this->abilityCache[$slug];
    }
}
