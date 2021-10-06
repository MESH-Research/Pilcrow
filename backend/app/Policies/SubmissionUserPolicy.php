<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubmissionUserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create the model
     *
     * @param  \App\Models\User  $user
     * @param  array  $model
     * @return bool
     */
    public function create(User $user, array $model)
    {
        // TODO: is user assigned to submission and is a review coordinator

        // TODO: Consider implementing a more maintainable pattern than a switch or series of if/else statements
        $is_assigning_a_reviewer = $model['role_id'] == 5; // TODO: Use a constant for this ID
        if ($is_assigning_a_reviewer) {
            return $user->can(Permission::ASSIGN_REVIEWER);
        }

        return true; // TODO: Check for the other roles
    }
}
