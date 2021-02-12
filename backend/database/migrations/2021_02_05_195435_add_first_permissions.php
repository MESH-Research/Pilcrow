<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddFirstPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permission_1 = Permission::create(['name' => Permission::UPDATE_USERS]);
        $permission_1->assignRole(Role::APPLICATION_ADMINISTRATOR);

        $permission_2 = Permission::create(['name' => Permission::UPDATE_USERS_IN_OWN_PUBLICATION]);
        $permission_2->assignRole(Role::PUBLICATION_ADMINISTRATOR);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /** @var $permission_1 Permission */
        $permission_1 = Permission::findByName(Permission::UPDATE_USERS);
        $permission_1->removeRole(Role::APPLICATION_ADMINISTRATOR);
        $permission_1->delete();

        /** @var $permission_2 Permission */
        $permission_2 = Permission::findByName(Permission::UPDATE_USERS_IN_OWN_PUBLICATION);
        $permission_2->removeRole(Role::PUBLICATION_ADMINISTRATOR);
        $permission_2->delete();
    }
}
