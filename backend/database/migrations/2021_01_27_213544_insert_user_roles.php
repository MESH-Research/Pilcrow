<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class InsertUserRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $guard_name = config('auth.defaults.guard');
        $roles = [
            'Application Administrator',
            'Publication Administrator',
            'Editor',
            'Review Coordinator',
            'Reviewer',
            'Submitter'
        ];

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
