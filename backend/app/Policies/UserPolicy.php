<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return bool
     */
    public function update(User $user, User $model)
    {
        //User is updating their own record.
        if ($user->id === $model->id) {
            return true;
        }
        //User has global permission to update users
        if ($user->can(Permission::UPDATE_USERS)) {
            return true;
        }

        // TODO: Check if user can update user within own publication
        // No explicit permission so return false.
        return false;
    }
}
