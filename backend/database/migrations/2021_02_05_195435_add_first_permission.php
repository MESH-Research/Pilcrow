<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddFirstPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permission = Permission::create(['name' => Permission::UPDATE_USER_FOR_OTHERS]);
        $permission->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $permission->assignRole(Role::PUBLICATION_ADMINISTRATOR);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /** @var $permission Permission */
        $permission = Permission::findByName(Permission::UPDATE_USER_FOR_OTHERS);
        $permission->removeRole(Role::APPLICATION_ADMINISTRATOR);
        $permission->removeRole(Role::PUBLICATION_ADMINISTRATOR);
        $permission->delete();
    }
}
