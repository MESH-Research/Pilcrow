<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Silber\Bouncer\BouncerFacade as Bouncer;

/**
 * Seeds the role rows and the global application-administrator grant.
 *
 * Scoped (publication / submission) role -> ability resolution does NOT live
 * here: that matrix is code-owned (App\Auth\RoleAbilities), read directly by
 * AbilityResolver, and never stored in Bouncer. This seeder only establishes
 * the role rows (whose titles surface as GraphQL Role.name) and the global
 * super-role wildcard. Global, runtime-editable abilities are granted via
 * Bouncer elsewhere.
 *
 * Idempotent — firstOrCreate / Bouncer::allow() are safe to re-run.
 */
class AbacSeeder extends Seeder
{
    /**
     * Seed the role rows and the application-administrator wildcard.
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

        Bouncer::refresh();
    }
}
