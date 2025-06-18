<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = Role::getArrayOfAllRoleNames();
        foreach ($roles as $key => $role) {
            Role::factory()->create([
                'id' => $key + 1,
                'name' => $role,
            ]);
        }

        Permission::findOrCreate(Permission::UPDATE_USERS)
            ->assignRole(Role::APPLICATION_ADMINISTRATOR);
        Permission::findOrCreate(Permission::UPDATE_USERS_IN_OWN_PUBLICATION)
            ->assignRole(Role::PUBLICATION_ADMINISTRATOR);
        Permission::findOrCreate(Permission::CREATE_PUBLICATION)
            ->assignRole(Role::APPLICATION_ADMINISTRATOR);
        Permission::findOrCreate(Permission::VIEW_ALL_PUBLICATIONS)
            ->assignRole(Role::APPLICATION_ADMINISTRATOR);

        Permission::findOrCreate(Permission::ASSIGN_REVIEWER)
            ->assignRole(Role::APPLICATION_ADMINISTRATOR)
            ->assignRole(Role::PUBLICATION_ADMINISTRATOR)
            ->assignRole(Role::EDITOR);

        Permission::findOrCreate(Permission::UNASSIGN_REVIEWER)
            ->assignRole(Role::APPLICATION_ADMINISTRATOR)
            ->assignRole(Role::PUBLICATION_ADMINISTRATOR)
            ->assignRole(Role::EDITOR);

        Permission::findOrCreate(Permission::ASSIGN_REVIEW_COORDINATOR)
            ->assignRole(Role::APPLICATION_ADMINISTRATOR)
            ->assignRole(Role::PUBLICATION_ADMINISTRATOR)
            ->assignRole(Role::EDITOR);

        Permission::findOrCreate(Permission::UNASSIGN_REVIEW_COORDINATOR)
            ->assignRole(Role::APPLICATION_ADMINISTRATOR)
            ->assignRole(Role::PUBLICATION_ADMINISTRATOR)
            ->assignRole(Role::EDITOR);

        Permission::findOrCreate(Permission::ASSIGN_EDITOR)
            ->assignRole(Role::APPLICATION_ADMINISTRATOR)
            ->assignRole(Role::PUBLICATION_ADMINISTRATOR);

        Permission::findOrCreate(Permission::UNASSIGN_EDITOR)
            ->assignRole(Role::APPLICATION_ADMINISTRATOR)
            ->assignRole(Role::PUBLICATION_ADMINISTRATOR);

        Permission::findOrCreate(Permission::UPDATE_SITE_SETTINGS)
            ->assignRole(Role::APPLICATION_ADMINISTRATOR);
    }
}
