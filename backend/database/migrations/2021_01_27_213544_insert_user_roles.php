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
            [
                'name' => Role::APPLICATION_ADMINISTRATOR,
            ],
            [
                'name' => Role::PUBLICATION_ADMINISTRATOR,
            ],
            [
                'name' => Role::EDITOR,
            ],
            [
                'name' => Role::REVIEW_COORDINATOR,
            ],
            [
                'name' => Role::REVIEWER,
            ],
            [
                'name' => Role::SUBMITTER,
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
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
