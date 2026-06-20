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
        return [
            Role::SLUG_PUBLICATION_ADMIN => [
                'publication.view',
                'publication.update',
                'submission.view',
                'submission.update',
                'submission.update-submitters',
                'submission.update-reviewers',
                'submission.update-review-coordinators',
                'submission.update-status',
                'submission.update-title',
                'submission.invite',
            ],
            Role::SLUG_EDITOR => [
                // Same as publication-administrator minus publication.update.
                'publication.view',
                'submission.view',
                'submission.update',
                'submission.update-submitters',
                'submission.update-reviewers',
                'submission.update-review-coordinators',
                'submission.update-status',
                'submission.update-title',
                'submission.invite',
            ],
            Role::SLUG_REVIEW_COORDINATOR => [
                'submission.view',
                'submission.update',
                'submission.update-submitters',
                'submission.update-reviewers',
                'submission.update-status',
                'submission.update-title',
                'submission.invite',
            ],
            Role::SLUG_SUBMITTER => [
                'submission.view',
                'submission.update',
                'submission.update-submitters',
                'submission.update-title',
                // Conditional: submitters may change status only while DRAFT.
                'submission.update-status' => static function ($entity): bool {
                    return $entity instanceof Submission
                        && $entity->status === Submission::DRAFT;
                },
            ],
            Role::SLUG_REVIEWER => [
                'submission.view',
                'submission.update',
            ],
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
