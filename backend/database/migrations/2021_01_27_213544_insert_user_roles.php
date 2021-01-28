<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
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
        $guard_name = config('auth.defaults.guard');

        DB::table('roles')->insert(
            [
                'name' => Role::APPLICATION_ADMINISTRATOR,
                'guard_name' => $guard_name
            ],
            [
                'name' => Role::PUBLICATION_ADMINISTRATOR,
                'guard_name' => $guard_name
            ],
            [
                'name' => Role::EDITOR,
                'guard_name' => $guard_name
            ],
            [
                'name' => Role::REVIEW_COORDINATOR,
                'guard_name' => $guard_name
            ],
            [
                'name' => Role::REVIEWER,
                'guard_name' => $guard_name
            ],
            [
                'name' => Role::SUBMITTER,
                'guard_name' => $guard_name
            ],
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // These records will get removed when the 'roles' table
        // is dropped in the previous migration file
    }
}
