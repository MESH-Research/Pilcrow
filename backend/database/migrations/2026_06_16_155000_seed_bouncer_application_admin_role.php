<?php
declare(strict_types=1);

use App\Auth\GlobalRole;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Silber\Bouncer\BouncerFacade as Bouncer;

/**
 * Cut the global application-administrator role over from spatie to Bouncer.
 *
 * This runs on `php artisan migrate` (unlike a seeder), so existing instances
 * get the Bouncer app-admin role and keep their administrators. It must run
 * AFTER the Bouncer tables exist (create_bouncer_tables) and BEFORE the spatie
 * tables are dropped (drop_spatie_permission_tables) — the spatie assignment
 * rows are read here to re-establish each admin in Bouncer.
 *
 * The role row and its id are owned by Bouncer; app code only names the slug
 * (App\Auth\GlobalRole). Scoped (publication / submission) roles are
 * intentionally NOT created here: they are not Bouncer roles (see ScopedRole).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Ensure the Bouncer app-admin role exists with its display title, then
        // grant it the global wildcard. Idempotent.
        Bouncer::role()->firstOrCreate(
            ['name' => GlobalRole::SLUG_APPLICATION_ADMIN],
            ['title' => GlobalRole::APPLICATION_ADMINISTRATOR]
        );
        Bouncer::allow(GlobalRole::SLUG_APPLICATION_ADMIN)->everything();
        Bouncer::refresh();

        $this->portExistingAdministrators();
    }

    public function down(): void
    {
        $role = Bouncer::role()->where('name', GlobalRole::SLUG_APPLICATION_ADMIN)->first();
        if ($role !== null) {
            Bouncer::disallow($role)->everything();
            $role->delete();
            Bouncer::refresh();
        }
        // Porting is not reversed: the spatie source tables are superseded.
    }

    /**
     * Re-assign every user who held the old spatie application-administrator
     * role to the Bouncer app-admin role, before the spatie tables are dropped.
     *
     * @return void
     */
    private function portExistingAdministrators(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('model_has_roles')) {
            return;
        }

        $spatieRoleId = \DB::table('roles')
            ->where('name', GlobalRole::APPLICATION_ADMINISTRATOR)
            ->value('id');
        if ($spatieRoleId === null) {
            return;
        }

        $userIds = \DB::table('model_has_roles')
            ->where('role_id', $spatieRoleId)
            ->where('model_type', User::class)
            ->pluck('model_id');

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user !== null) {
                $user->assign(GlobalRole::SLUG_APPLICATION_ADMIN);
            }
        }
    }
};
