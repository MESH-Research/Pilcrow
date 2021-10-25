<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

// TODO: Use constants for the ID usages
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
            case '6':
            case '5':
            case '4':
                return false;
            case '3':
                return $this->assignEditor($user);
            case '2':
            case '1':
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
            case '6':
            case '5':
            case '4':
                return false;
            case '3':
                return $this->unassignEditor($user);
            case '2':
            case '1':
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
