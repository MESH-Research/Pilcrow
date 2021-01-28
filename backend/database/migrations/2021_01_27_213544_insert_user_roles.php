<?php

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
        $roles = [
            Role::APPLICATION_ADMINISTRATOR,
            Role::PUBLICATION_ADMINISTRATOR,
            Role::EDITOR,
            Role::REVIEW_COORDINATOR,
            Role::REVIEWER,
            Role::SUBMITTER,
        ];

        foreach ($roles as $role) {
            Role::create([
                'name' => $role
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
        // These records will get removed when the 'roles' table
        // is dropped in the previous migration file
    }
}
