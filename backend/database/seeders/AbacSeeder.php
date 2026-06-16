<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
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
     * Seed the role -> ability registry.
     *
     * @return void
     */
    public function run(): void
    {
        // Create each role with its human-readable title (surfaced as
        // GraphQL Role.name). Bouncer::allow() would otherwise create them
        // title-less.
        foreach (Role::SLUG_TO_TITLE as $slug => $title) {
            Role::firstOrCreate(['name' => $slug], ['title' => $title]);
        }

        Bouncer::allow(Role::SLUG_APPLICATION_ADMIN)->everything();

        foreach (self::MATRIX as $role => $abilities) {
            foreach ($abilities as $ability) {
                Bouncer::allow($role)->to($ability);
            }
        }

        Bouncer::refresh();
    }
}
