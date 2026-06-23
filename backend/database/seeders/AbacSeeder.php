<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Auth\GlobalRole;
use Illuminate\Database\Seeder;
use Silber\Bouncer\BouncerFacade as Bouncer;

/**
 * Ensures the global application-administrator Bouncer role exists, for
 * fresh-install (db:seed) and test bootstraps.
 *
 * The same role + grant is established for real deploys by the
 * seed_bouncer_application_admin_role migration (which also ports existing
 * admins); this seeder is the idempotent fresh-DB equivalent.
 *
 * Scoped (publication / submission) roles are NOT seeded: they are not Bouncer
 * roles. The scoped role -> ability map is code-owned (App\Auth\ScopedRole),
 * read directly by ScopedAbilityResolver, and never stored in Bouncer.
 */
class AbacSeeder extends Seeder
{
    /**
     * Seed the application-administrator role and its global wildcard grant.
     *
     * @return void
     */
    public function run(): void
    {
        Bouncer::role()->firstOrCreate(
            ['name' => GlobalRole::ApplicationAdministrator->toSlug()],
            ['title' => GlobalRole::ApplicationAdministrator->title()]
        );

        Bouncer::allow(GlobalRole::ApplicationAdministrator->toSlug())->everything();

        Bouncer::refresh();
    }
}
