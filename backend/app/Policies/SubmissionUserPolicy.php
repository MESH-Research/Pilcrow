<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use App\Models\SubmissionUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubmissionUserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create the model
     *
     * TODO: Consider implementing a more maintainable pattern than a switch or series of if/else statements
     * TODO: Expand policy for the creation of other roles of submission users besides reviewers
     *
     * @param  \App\Models\User  $user
     * @param  array  $model
     * @return bool
     */
    public function create(User $user, array $model)
    {
        $is_assigning_a_reviewer = $model['role_id'] == 5; // TODO: Use a constant for this ID
        if ($is_assigning_a_reviewer) {
            return $user->can(Permission::ASSIGN_REVIEWER);
        }

        $is_assigned_as_a_review_coordinator = SubmissionUser::where('user_id', $user->id,)
            ->where('role_id', 4) // TODO: Use a constant for this ID
            ->where('submission_id', $model['submission_id'])->first();
        if ($is_assigned_as_a_review_coordinator && $is_assigning_a_reviewer) {
            return true;
        }

        return false;
    }
}
