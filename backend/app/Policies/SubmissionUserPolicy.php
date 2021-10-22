<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Permission;
use App\Models\SubmissionUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

// TODO: Use constants for the ID usages
class SubmissionUserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create a submission user record
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
                return false;
            case '5':
                return $this->assignReviewer($user, $model);
            case '4':
                return $this->assignReviewCoordinator($user);
            case '3':
                return $this->assignEditor($user);
            case '2':
            case '1':
                return false;
        }

        return false;
    }

    /**
     * Determine whether the user can delete a submission user record
     * This mirrors the same permissions for creating/assigning
     *
     * @param  \App\Models\User  $user
     * @param  array  $model
     * @return bool
     */
    public function delete(User $user, array $model)
    {
        return $this->create($user, $model);
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
            return $user->can(Permission::ASSIGN_REVIEWER);
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
     * @return bool
     */
    private function assignReviewCoordinator(User $user)
    {
        // Assigning user has a higher privileged role
        if ($user->getHighestPrivilegedRole()) {
            return $user->can(Permission::ASSIGN_REVIEWER);
        }

        return false;
    }

    /**
     * Determine whether the user can assign a review coordinator to a submission
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
}
