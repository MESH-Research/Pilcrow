<?php
declare(strict_types=1);

namespace App\Auth;

use App\Auth\Abilities\ScopedAbility;
use App\Auth\Roles\ScopedRole;
use App\Models\Contracts\Comment;
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
     * Per-instance memo of resolved effective roles, keyed by user + entity.
     *
     * The client-facing `abilities()` resolvers ask this service for every
     * ability case on the same entity, so without memoization a single
     * submission node re-runs the pivot lookups N times. The cache is scoped to
     * the resolver instance (not a request-wide singleton) so it cannot serve a
     * stale verdict across a role-mutating request.
     *
     * @var array<string, array<int, \App\Auth\Roles\ScopedRole>>
     */
    private array $effectiveRoleCache = [];

    /**
     * Is the scoped ability granted to the user for the given entity?
     *
     * Only the app-admin ROLE short-circuits (never a Bouncer ability); then the
     * user's effective scoped roles for the entity decide.
     *
     * @param \App\Models\User $user
     * @param \App\Auth\Abilities\ScopedAbility $ability
     * @param \App\Models\Publication|\App\Models\Submission|\App\Models\Contracts\Comment|null $entity
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
     * @param \App\Models\Publication|\App\Models\Submission|\App\Models\Contracts\Comment|null $entity
     * @return array<int, \App\Auth\Roles\ScopedRole>
     */
    public function effectiveRoles(User $user, $entity = null): array
    {
        if ($entity === null) {
            return [];
        }

        $cacheKey = $user->id . ':' . $entity::class . ':' . $entity->getKey();
        if (isset($this->effectiveRoleCache[$cacheKey])) {
            return $this->effectiveRoleCache[$cacheKey];
        }

        $roles = [];

        if ($entity instanceof Publication) {
            $roles = $this->publicationRolesFor($user, $entity);
        } elseif ($entity instanceof Submission) {
            // Direct submission roles plus admin roles inherited from the
            // parent publication (publication admin / editor). The pivots are
            // unique on (user, entity) and publication vs submission role slugs
            // are disjoint, so this merge can never yield a duplicate role.
            $roles = array_merge(
                $this->submissionRolesFor($user, $entity),
                $this->submissionPublicationRoles($user, $entity)
            );
        } elseif ($entity instanceof Comment) {
            // A comment carries no roles of its own — its authorization is the
            // submission's. Resolve roles against the owning submission; the
            // CommentAbility predicate then ties the grant to authorship of THIS
            // comment.
            $submission = $entity->submission;
            $roles = $submission instanceof Submission
                ? array_merge(
                    $this->submissionRolesFor($user, $submission),
                    $this->submissionPublicationRoles($user, $submission)
                )
                : [];
        }

        return $this->effectiveRoleCache[$cacheKey] = $roles;
    }

    /**
     * The user's direct roles on a submission. Reads the loaded
     * submissionAssignments relation when present — so an eager-loaded list
     * endpoint resolves abilities with no per-row pivot query — and falls back to
     * a targeted query otherwise.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return array<int, \App\Auth\Roles\ScopedRole>
     */
    private function submissionRolesFor(User $user, Submission $submission): array
    {
        if ($submission->relationLoaded('submissionAssignments')) {
            $roleSlugs = $submission->submissionAssignments
                ->where('user_id', $user->id)
                ->pluck('role')
                ->all();

            return $this->rolesForSlugs($roleSlugs);
        }

        $roleSlugs = SubmissionAssignment::query()
            ->where('user_id', $user->id)
            ->where('submission_id', $submission->id)
            ->pluck('role')
            ->all();

        return $this->rolesForSlugs($roleSlugs);
    }

    /**
     * The publication-admin roles a user inherits onto a submission through its
     * parent publication. Prefers the loaded `publication` relation (and its
     * loaded assignments), else queries by publication_id.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return array<int, \App\Auth\Roles\ScopedRole>
     */
    private function submissionPublicationRoles(User $user, Submission $submission): array
    {
        if ($submission->relationLoaded('publication') && $submission->publication !== null) {
            return $this->publicationRolesFor($user, $submission->publication);
        }

        return $this->publicationRolesByIdQuery($user, $submission->publication_id);
    }

    /**
     * The user's roles on a publication. Reads the loaded publicationAssignments
     * relation when present, else queries the pivot.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Publication $publication
     * @return array<int, \App\Auth\Roles\ScopedRole>
     */
    private function publicationRolesFor(User $user, Publication $publication): array
    {
        if ($publication->relationLoaded('publicationAssignments')) {
            $roleSlugs = $publication->publicationAssignments
                ->where('user_id', $user->id)
                ->pluck('role')
                ->all();

            return $this->rolesForSlugs($roleSlugs);
        }

        return $this->publicationRolesByIdQuery($user, $publication->id);
    }

    /**
     * @param \App\Models\User $user
     * @param string|int $publicationId
     * @return array<int, \App\Auth\Roles\ScopedRole>
     */
    private function publicationRolesByIdQuery(User $user, $publicationId): array
    {
        $roleSlugs = PublicationAssignment::query()
            ->where('user_id', $user->id)
            ->where('publication_id', $publicationId)
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
