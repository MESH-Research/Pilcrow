<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Permission;
use App\Models\SubmissionUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

// TODO: Expand policy for the creation of other roles of submission users besides reviewers
// TODO: Use constants for the ID usages
class SubmissionUserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create the model
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
            case 5:
                $this->assignReviewer($user, $model);
                break;
        }
        return true;
    }

    /**
     * Determine whether the user can assign a reviewer to a submission
     *
     * @param  \App\Models\User  $user
     * @param  array  $model
     * @return bool
     */
    private function assignReviewer(User $user, array $model)
    {
        // User has higher privileged role
        if ($user->highestPrivilegedRole()) {
            return $user->can(Permission::ASSIGN_REVIEWER);
        }
        // User is assigned as a Review Coordinator to the submission
        return SubmissionUser::where('user_id', $user->id)
            ->where('role_id', 4)
            ->where('submission_id', $model['submission_id'])
            ->exists();
    }
}
