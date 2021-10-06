<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Permission;
use App\Models\Role;

class AddReviewerAssignmentPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permission_1 = Permission::create(['name' => Permission::ASSIGN_REVIEWER]);
        $permission_1->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $permission_1->assignRole(Role::PUBLICATION_ADMINISTRATOR);
        $permission_1->assignRole(Role::EDITOR);
        $permission_1->assignRole(Role::REVIEW_COORDINATOR);

        $permission_2 = Permission::create(['name' => Permission::UNASSIGN_REVIEWER]);
        $permission_2->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $permission_2->assignRole(Role::PUBLICATION_ADMINISTRATOR);
        $permission_2->assignRole(Role::EDITOR);
        $permission_2->assignRole(Role::REVIEW_COORDINATOR);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /** @var $permission_1 Permission */
        $permission_1 = Permission::findByName(Permission::ASSIGN_REVIEWER);
        $permission_1->removeRole(Role::APPLICATION_ADMINISTRATOR);
        $permission_1->assignRole(Role::PUBLICATION_ADMINISTRATOR);
        $permission_1->assignRole(Role::EDITOR);
        $permission_1->assignRole(Role::REVIEW_COORDINATOR);
        $permission_1->delete();

        /** @var $permission_2 Permission */
        $permission_2 = Permission::findByName(Permission::UNASSIGN_REVIEWER);
        $permission_2->removeRole(Role::APPLICATION_ADMINISTRATOR);
        $permission_2->assignRole(Role::PUBLICATION_ADMINISTRATOR);
        $permission_2->assignRole(Role::EDITOR);
        $permission_2->assignRole(Role::REVIEW_COORDINATOR);
        $permission_2->delete();
    }
}
