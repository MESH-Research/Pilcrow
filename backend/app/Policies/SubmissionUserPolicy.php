<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
// use App\Models\Permission;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubmissionUserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create the model
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return bool
    */
    public function create(User $user, User $model)
    {
        // print_r($user->toArray());
        // print_r($model->toArray());
        // $is_assigning_a_reviewer = $model->role_id == 5;
        // if ($is_assigning_a_reviewer && $user->can(Permission::ASSIGN_REVIEWER)) {
        //     return true;
        // }
        // return false;
        return true;
    }
}
