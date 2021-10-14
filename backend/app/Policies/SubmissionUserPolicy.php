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
            case '5':
                return $this->assignReviewer($user, $model);
            case '4':
                return $this->assignReviewCoordinator($user, $model);
        }

        return false;
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
        // Assigning user has a higher privileged role
        if ($user->getHighestPrivilegedRole()) {
            $permission = $user->can(Permission::ASSIGN_REVIEWER);

            return $permission;
        }
        // Assigning user is a Review Coordinator of the submission
        return SubmissionUser::where('user_id', $user->id)
            ->where('role_id', 4)
            ->where('submission_id', $model['submission_id'])
            ->exists();
    }

    /**
     * Determine whether the user can assign a review coordinator to a submission
     *
     * @param  \App\Models\User  $user
     * @param  array  $model
     * @return bool
     */
    private function assignReviewCoordinator(User $user, array $model)
    {
        // Assigning user has a higher privileged role
        if ($user->getHighestPrivilegedRole()) {
            $permission = $user->can(Permission::ASSIGN_REVIEWER);

            return $permission;
        }
        return false;
    }
}
