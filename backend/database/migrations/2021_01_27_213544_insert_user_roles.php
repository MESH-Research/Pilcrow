<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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
        DB::table('roles')->insert(
            [
                'name' => Role::APPLICATION_ADMINISTRATOR
            ],
            [
                'name' => Role::PUBLICATION_ADMINISTRATOR
            ],
            [
                'name' => Role::EDITOR
            ],
            [
                'name' => Role::REVIEW_COORDINATOR
            ],
            [
                'name' => Role::REVIEWER
            ],
            [
                'name' => Role::SUBMITTER
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
        // These records will get removed when the 'roles' table is dropped
    }
}
