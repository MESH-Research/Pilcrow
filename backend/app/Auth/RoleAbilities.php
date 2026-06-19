<?php
declare(strict_types=1);

namespace App\Auth;

use App\Models\Role;

/**
 * The scoped role -> ability map.
 *
 * Code is the single source of truth for scoped (publication / submission)
 * permissions: this matrix is read directly by AbilityResolver at request time.
 * It is intentionally NOT runtime-editable and NOT stored in Bouncer — a change
 * ships in code and is live on deploy, with no seeding, convergence, or drift.
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
     * role slug => list of granted ability names.
     *
     * @var array<string, array<int, string>>
     */
    public const MATRIX = [
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
            // submitters may only change status while DRAFT; the policy gates
            // this draft-only variant on the submission's status.
            'submission.update-status-draft',
            'submission.update-title',
        ],
        Role::SLUG_REVIEWER => [
            'submission.view',
            'submission.update',
        ],
    ];

    /**
     * Granted ability names for a role slug.
     *
     * @param string $slug
     * @return array<int, string>
     */
    public static function for(string $slug): array
    {
        return self::MATRIX[$slug] ?? [];
    }
}
