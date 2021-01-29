<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

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
        $guard_name = config('auth.defaults.guard');

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'name' => $role,
                'guard_name' => $guard_name,
                'created_at' => null,
                'updated_at' => null
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('roles')->truncate();
        Schema::enableForeignKeyConstraints();
    }
}
