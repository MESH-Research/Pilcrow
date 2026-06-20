<?php
declare(strict_types=1);

namespace App\Auth;

use App\Models\Role;
use App\Models\Submission;

/**
 * The scoped role -> ability map.
 *
 * Code is the single source of truth for scoped (publication / submission)
 * permissions: this matrix is read directly by AbilityResolver at request time.
 * It is intentionally NOT runtime-editable and NOT stored in Bouncer — a change
 * ships in code and is live on deploy, with no seeding, convergence, or drift.
 *
 * Each role maps to a list of grants. A grant is either:
 *   - a bare ability string  → absolute grant; or
 *   - 'ability' => predicate → conditional grant, allowed only when the
 *     predicate (a callable receiving the entity) returns true.
 * The attribute condition is thus data attached to the grant, evaluated by the
 * resolver, rather than a special-cased ability name + policy branch. Because
 * closures cannot live in a const, the matrix is a method.
 *
 * Scoping — who holds which role on which entity — lives in the
 * publication_user / submission_user pivots. This only defines what each role
 * may do. application_admin is the global super-role (granted everything) and
 * is short-circuited in the resolver, so it has no entry here.
 *
 * Global, runtime-editable abilities (e.g. publication.create, avatar.upload)
 * are NOT here — those live in Bouncer and are checked via $user->can().
 */
class RoleAbilities
{
    /**
     * role slug => list of grants (bare ability string, or ability => predicate).
     *
     * @return array<string, array<int|string, string|callable>>
     */
    public static function matrix(): array
    {
        // Roles compose as supersets: each builds on the one below it with the
        // spread operator, so the "everything from role B, plus X" relationship
        // is explicit. (array_values is deliberately NOT used — it would strip
        // the string keys that carry the conditional grants below.)
        $reviewer = [
            'submission.view',
            'submission.update',
        ];

        $reviewCoordinator = [
            ...$reviewer,
            'submission.update-submitters',
            'submission.update-reviewers',
            'submission.update-status',
            'submission.update-title',
            'submission.invite',
        ];

        $editor = [
            ...$reviewCoordinator,
            'publication.view',
            'submission.update-review-coordinators',
        ];

        $publicationAdmin = [
            ...$editor,
            'publication.update',
        ];

        // Submitter is not in the coordinator chain: it adds title/submitter
        // edits to a reviewer, and may change status only while DRAFT — a
        // conditional grant (string key => predicate).
        $submitter = [
            ...$reviewer,
            'submission.update-submitters',
            'submission.update-title',
            'submission.update-status' => static function ($entity): bool {
                return $entity instanceof Submission
                    && $entity->status === Submission::DRAFT;
            },
        ];

        return [
            Role::SLUG_PUBLICATION_ADMIN => $publicationAdmin,
            Role::SLUG_EDITOR => $editor,
            Role::SLUG_REVIEW_COORDINATOR => $reviewCoordinator,
            Role::SLUG_SUBMITTER => $submitter,
            Role::SLUG_REVIEWER => $reviewer,
        ];
    }

    /**
     * Does the given role grant the ability for the entity?
     *
     * Bare (integer-keyed) entries are absolute grants. String-keyed entries
     * carry a predicate that must hold for the entity.
     *
     * @param string $slug
     * @param string $ability
     * @param \App\Models\Publication|\App\Models\Submission|null $entity
     */
    public static function grants(string $slug, string $ability, $entity = null): bool
    {
        foreach (self::matrix()[$slug] ?? [] as $key => $value) {
            if (is_int($key)) {
                if ($value === $ability) {
                    return true;
                }
                continue;
            }

            if ($key === $ability) {
                return $entity !== null && $value($entity);
            }
        }

        return false;
    }
}
