<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SubmissionPolicy
{
    use HandlesAuthorization;

    /**
     * Check admin roles
     *
     * @param \App\Models\User $user
     * @param int $publicationId
     * @return bool
     */
    protected function checkAdminRoles(User $user, $publicationId)
    {
        if ($user->hasRole(Role::APPLICATION_ADMINISTRATOR)) {
            return true;
        }

        //Check if the user has a publication role
        if (
            $user->hasPublicationRole(
                [Role::PUBLICATION_ADMINISTRATOR_ROLE_ID, Role::EDITOR_ROLE_ID],
                $publicationId
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * updateSubmitters
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function updateSubmitters(User $user, Submission $submission)
    {
        if ($this->checkAdminRoles($user, $submission->publication_id)) {
            return true;
        }

        //Check if the user is a submitter or review coordinator
        if ($user->hasSubmissionRole([Role::REVIEW_COORDINATOR_ROLE_ID, Role::SUBMITTER_ROLE_ID], $submission->id)) {
            return true;
        }

        return Response::deny('You do not have permission to edit submitters for this submission');
    }

    /**
     * update Reviewers policy check
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function updateReviewers(User $user, Submission $submission)
    {
        if ($this->checkAdminRoles($user, $submission->publication_id)) {
            return true;
        }

        //Check if the user is a review_coordinator
        if ($user->hasSubmissionRole(Role::REVIEW_COORDINATOR_ROLE_ID, $submission->id)) {
            return true;
        }

        return Response::deny('You do not have permission to update reviewers for this submission');
    }

    /**
     * update review_coordinators policy check
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function updateReviewCoordinators(User $user, Submission $submission)
    {
        if ($this->checkAdminRoles($user, $submission->publication_id)) {
            return true;
        }
    }

    /**
     * Update submission status policy
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function updateStatus(User $user, Submission $submission)
    {
        if ($this->checkAdminRoles($user, $submission->publication_id)) {
            return true;
        }

        if ($user->hasSubmissionRole([Role::REVIEW_COORDINATOR_ROLE_ID], $submission->id)) {
            return true;
        }

        return Response::deny('You do not have permission to update the status of this submission');
    }

    /**
     * View submission policy
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function view(User $user, Submission $submission)
    {
        if ($this->checkAdminRoles($user, $submission->publication_id)) {
            return true;
        }

        //Check that the user has any role on the submission

        if ($user->hasSubmissionRole('*', $submission->id)) {
            return true;
        }

        return Response::deny('You do not have permission to view this submission');
    }

    /**
     * Update Submission Policy
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function update(User $user, Submission $submission)
    {
        if ($this->checkAdminRoles($user, $submission->publication_id)) {
            return true;
        }

        //Check that the user has any role on the submission

        if ($user->hasSubmissionRole('*', $submission->id)) {
            return true;
        }

        return Response::deny('You do not have permission to update this submission');
    }
}
