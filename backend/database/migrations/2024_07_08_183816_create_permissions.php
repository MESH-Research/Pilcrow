<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roles = Role::getArrayOfAllRoleNames();
        foreach ($roles as $key => $role) {
            Role::factory()->create([
                'id' => $key + 1,
                'name' => $role,
            ]);
        }

        Permission::create(['name' => Permission::UPDATE_USERS])
            ->assignRole(Role::APPLICATION_ADMINISTRATOR);
        Permission::create(['name' => Permission::UPDATE_USERS_IN_OWN_PUBLICATION])
            ->assignRole(Role::PUBLICATION_ADMINISTRATOR);
        Permission::create(['name' => Permission::CREATE_PUBLICATION])
            ->assignRole(Role::APPLICATION_ADMINISTRATOR);
        Permission::create(['name' => Permission::VIEW_ALL_PUBLICATIONS])
            ->assignRole(Role::APPLICATION_ADMINISTRATOR);

        Permission::create(['name' => Permission::ASSIGN_REVIEWER])
            ->assignRole(Role::APPLICATION_ADMINISTRATOR)
            ->assignRole(Role::PUBLICATION_ADMINISTRATOR)
            ->assignRole(Role::EDITOR);

        Permission::create(['name' => Permission::UNASSIGN_REVIEWER])
            ->assignRole(Role::APPLICATION_ADMINISTRATOR)
            ->assignRole(Role::PUBLICATION_ADMINISTRATOR)
            ->assignRole(Role::EDITOR);

        Permission::create(['name' => Permission::ASSIGN_REVIEW_COORDINATOR])
            ->assignRole(Role::APPLICATION_ADMINISTRATOR)
            ->assignRole(Role::PUBLICATION_ADMINISTRATOR)
            ->assignRole(Role::EDITOR);

        Permission::create(['name' => Permission::UNASSIGN_REVIEW_COORDINATOR])
            ->assignRole(Role::APPLICATION_ADMINISTRATOR)
            ->assignRole(Role::PUBLICATION_ADMINISTRATOR)
            ->assignRole(Role::EDITOR);

        Permission::create(['name' => Permission::ASSIGN_EDITOR])
            ->assignRole(Role::APPLICATION_ADMINISTRATOR)
            ->assignRole(Role::PUBLICATION_ADMINISTRATOR);

        Permission::create(['name' => Permission::UNASSIGN_EDITOR])
            ->assignRole(Role::APPLICATION_ADMINISTRATOR)
            ->assignRole(Role::PUBLICATION_ADMINISTRATOR);

        Permission::create(['name' => Permission::UPDATE_SITE_SETTINGS])
            ->assignRole(Role::APPLICATION_ADMINISTRATOR);
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
