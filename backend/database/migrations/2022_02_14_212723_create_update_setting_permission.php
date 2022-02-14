<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

class CreateUpdateSettingPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permission = Permission::create(['name' => Permission::UPDATE_SITE_SETTINGS]);
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
        $permission = Permission::findByName(Permission::UPDATE_SITE_SETTINGS);
        $permission->removeRole(Role::APPLICATION_ADMINISTRATOR);
        $permission->delete();
    }
}

