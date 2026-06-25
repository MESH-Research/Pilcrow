<?php
declare(strict_types=1);

use App\Auth\Roles\GlobalRole;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Silber\Bouncer\BouncerFacade as Bouncer;

/**
 * Cut the global application-administrator role over from spatie to Bouncer.
 *
 * This runs on `php artisan migrate` (unlike a seeder), so existing instances
 * get the Bouncer app-admin role and keep their administrators. It must run
 * AFTER the Bouncer tables exist (create_bouncer_tables); the spatie assignment
 * rows are read here to re-establish each admin in Bouncer.
 *
 * This cutover is deliberately EXPAND-ONLY: the spatie tables are NOT dropped.
 * They are left intact (and the ported rows still present in both systems) so a
 * revert by redeploying the pre-slug code keeps working without a snapshot —
 * old code finds its spatie app-admin rows and the retained, dual-written
 * pivot role_id. Dropping the spatie tables is a later, separate contract PR
 * (alongside dropping role_id) once this is proven in production.
 *
 * The role row and its id are owned by Bouncer; app code only names the slug
 * (App\Auth\Roles\GlobalRole). Scoped (publication / submission) roles are
 * intentionally NOT created here: they are not Bouncer roles (see ScopedRole).
 */
return new class extends Migration
{
    /**
     * The legacy spatie `roles.name` of the application administrator, pinned as
     * a literal so porting can never be silently broken by a later edit to the
     * @deprecated GlobalRole::title() (which happens to return the same string
     * today). This value is frozen historical data, not a live label.
     */
    private const LEGACY_SPATIE_ADMIN_NAME = 'Application Administrator';

    public function up(): void
    {
        // Ensure the Bouncer app-admin role exists with its display title, then
        // grant it the global wildcard. Idempotent.
        Bouncer::role()->firstOrCreate(
            ['name' => GlobalRole::ApplicationAdministrator->toSlug()],
            ['title' => GlobalRole::ApplicationAdministrator->title()]
        );
        Bouncer::allow(GlobalRole::ApplicationAdministrator->toSlug())->everything();
        Bouncer::refresh();

        $this->portExistingAdministrators();
    }

    public function down(): void
    {
        $role = Bouncer::role()->where('name', GlobalRole::ApplicationAdministrator->toSlug())->first();
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
            ->where('name', self::LEGACY_SPATIE_ADMIN_NAME)
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
                $user->assign(GlobalRole::ApplicationAdministrator->toSlug());
            }
        }
    }
};
