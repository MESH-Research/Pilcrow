<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Silber\Bouncer\BouncerFacade as Bouncer;

/**
 * Seeds the ABAC ability registry: the role -> ability map.
 *
 * This is the data-driven replacement for the hardcoded role-id lists that
 * used to live inside the policies. Adding a capability = add an ability name
 * to the matrix below. Scoping (who holds which role where) stays in the
 * publication_user / submission_user pivots; this only defines what each role
 * can do.
 *
 * Idempotent — Bouncer::allow() is firstOrCreate under the hood.
 */
class AbacSeeder extends Seeder
{
    /**
     * role slug => list of granted ability names.
     *
     * application-administrator is granted everything separately (wildcard).
     *
     * @var array<string, array<int, string>>
     */
    public const MATRIX = [
        'publication-administrator' => [
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
        'editor' => [
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
        'review-coordinator' => [
            'submission.view',
            'submission.update',
            'submission.update-submitters',
            'submission.update-reviewers',
            'submission.update-status',
            'submission.update-title',
            'submission.invite',
        ],
        'submitter' => [
            'submission.view',
            'submission.update',
            'submission.update-submitters',
            // gated to DRAFT by a policy predicate:
            'submission.update-status',
            'submission.update-title',
        ],
        'reviewer' => [
            'submission.view',
            'submission.update',
        ],
    ];

    public function run(): void
    {
        Bouncer::allow('application-administrator')->everything();

        foreach (self::MATRIX as $role => $abilities) {
            foreach ($abilities as $ability) {
                Bouncer::allow($role)->to($ability);
            }
        }

        Bouncer::refresh();
    }
}
