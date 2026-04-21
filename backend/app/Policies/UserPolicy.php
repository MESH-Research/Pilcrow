<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can list user accounts.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasRole(Role::APPLICATION_ADMINISTRATOR);
    }

    /**
     * Determine whether the user can view another user's details.
     *
     * Users querying their own data should use the `currentUser` query;
     * this field is reserved for application administrators. The target
     * $model is accepted to match Lighthouse's @can(find:) contract but
     * is not consulted — access depends only on the caller's role.
     *
     * @param \App\Models\User $user
     * @param \App\Models\User $model
     * @return bool
     */
    public function view(User $user, User $model)
    {
        unset($model);

        return $user->hasRole(Role::APPLICATION_ADMINISTRATOR);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User  $user
     * @param \App\Models\User  $model
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
