<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

class InsertUserRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $roles = Role::getArrayOfAllRoleNames();
        foreach ($roles as $key => $role) {
            $role = Role::create(['name' => $role]);
            $role->id = $key + 1;
            $role->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $roles = Role::getArrayOfAllRoleNames();
        foreach ($roles as $role) {
            /** @var $role Role */
            $role = Role::findByName($role);
            $role->delete();
        }
    }
}
