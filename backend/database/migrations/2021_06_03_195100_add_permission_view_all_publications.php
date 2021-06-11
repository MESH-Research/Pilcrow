<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddPermissionViewAllPublications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permission = Permission::create(['name' => Permission::VIEW_ALL_PUBLICATIONS]);
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
        $permission = Permission::findByName(Permission::VIEW_ALL_PUBLICATIONS);
        $permission->removeRole(Role::APPLICATION_ADMINISTRATOR);
        $permission->delete();

    }
}
