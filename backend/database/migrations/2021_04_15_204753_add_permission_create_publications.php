<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddPermissionCreatePublications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permission = Permission::create(['name' => Permission::CREATE_PUBLICATION]);
        $permission->assignRole(Role::APPLICATION_ADMINISTRATOR);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /** @var $permission Permission */
        $permission = Permission::findByName(Permission::CREATE_PUBLICATION);
        $permission->removeRole(Role::APPLICATION_ADMINISTRATOR);
        $permission->delete();
    }
}
