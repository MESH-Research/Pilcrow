<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublicationUserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create a publication user record
     *
     * TODO: Consider implementing a more maintainable pattern than a switch or series of if/else statements
     *
     * @param  \App\Models\User  $user
     * @param  array  $model
     * @return bool
     */
    public function create(User $user, array $model)
    {
        switch ($model['role_id']) {
            case Role::SUBMITTER_ROLE_ID:
            case Role::REVIEWER_ROLE_ID:
            case Role::REVIEW_COORDINATOR_ROLE_ID:
                return false;
            case Role::EDITOR_ROLE_ID:
                return $this->assignEditor($user);
            case Role::PUBLICATION_ADMINISTRATOR_ROLE_ID:
            case Role::APPLICATION_ADMINISTRATOR_ROLE_ID:
                return false;
        }

        return false;
    }

    /**
     * Determine whether the user can delete a publication user record
     *
     * TODO: Consider implementing a more maintainable pattern than a switch or series of if/else statements
     *
     * @param  \App\Models\User  $user
     * @param  array  $model
     * @return bool
     */
    public function delete(User $user, array $model)
    {
        switch ($model['role_id']) {
            case Role::SUBMITTER_ROLE_ID:
            case Role::REVIEWER_ROLE_ID:
            case Role::REVIEW_COORDINATOR_ROLE_ID:
                return false;
            case Role::EDITOR_ROLE_ID:
                return $this->unassignEditor($user);
            case Role::PUBLICATION_ADMINISTRATOR_ROLE_ID:
            case Role::APPLICATION_ADMINISTRATOR_ROLE_ID:
                return false;
        }

        return false;
    }

    /**
     * Determine whether the user can assign an editor to a publication
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    private function assignEditor(User $user)
    {
        // Assigning user has a higher privileged role
        if ($user->getHighestPrivilegedRole()) {
            return $user->can(Permission::ASSIGN_EDITOR);
        }

        return false;
    }

    /**
     * Determine whether the user can unassign an editor to a publication
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    private function unassignEditor(User $user)
    {
        // Unassigning user has a higher privileged role
        if ($user->getHighestPrivilegedRole()) {
            return $user->can(Permission::UNASSIGN_EDITOR);
        }

        return false;
    }
}
