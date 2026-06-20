<?php
declare(strict_types=1);

use App\Models\Role;
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
 * Scoped (publication / submission) roles are intentionally NOT created here:
 * they are not Bouncer roles (see App\Auth\ScopedRole).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Ensure the Bouncer app-admin role exists with its display title, then
        // grant it the global wildcard. Idempotent.
        $role = Role::firstOrCreate(
            ['name' => Role::SLUG_APPLICATION_ADMIN],
            ['title' => Role::APPLICATION_ADMINISTRATOR]
        );
        Bouncer::allow($role)->everything();
        Bouncer::refresh();

        $this->portExistingAdministrators($role);
    }

    public function down(): void
    {
        $role = Role::where('name', Role::SLUG_APPLICATION_ADMIN)->first();
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
     * @param \App\Models\Role $role
     * @return void
     */
    private function portExistingAdministrators(Role $role): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('model_has_roles')) {
            return;
        }

        $spatieRoleId = \DB::table('roles')
            ->where('name', Role::APPLICATION_ADMINISTRATOR)
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
                $user->assign($role);
            }
        }
    }
};
