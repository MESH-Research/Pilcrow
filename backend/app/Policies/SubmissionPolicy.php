<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\InlineComment;
use App\Models\OverallComment;
use App\Models\Publication;
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
     * Check if a submission can be created
     *
     * @param \App\Models\User $user
     * @param array $args
     * @return bool
     */
    public function create(User $user, $args)
    {
        //Check if the publication is rejecting submissions
        $publication_id = $args['publication_id'];
        $publication = Publication::where('id', $publication_id)->firstOrFail();

        return $publication->is_accepting_submissions;
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

        return Response::deny('UNAUTHORIZED');
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
        if ($user->hasSubmissionRole([Role::REVIEW_COORDINATOR_ROLE_ID], $submission->id)) {
            return true;
        }

        return Response::deny('UNAUTHORIZED');
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

        return Response::deny('UNAUTHORIZED');
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

        if (
            $user->hasSubmissionRole([Role::SUBMITTER_ROLE_ID], $submission->id) &&
            ($submission->status == Submission::DRAFT)
        ) {
            return true;
        }

        return Response::deny('UNAUTHORIZED');
    }

    /**
     * Update submission title policy
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function updateTitle(User $user, Submission $submission)
    {
        if ($this->checkAdminRoles($user, $submission->publication_id)) {
            return true;
        }

        if ($user->hasSubmissionRole([Role::REVIEW_COORDINATOR_ROLE_ID], $submission->id)) {
            return true;
        }

        if ($user->hasSubmissionRole([Role::SUBMITTER_ROLE_ID], $submission->id)) {
            return true;
        }

        return Response::deny('UNAUTHORIZED');
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

        return Response::deny('UNAUTHORIZED');
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

        return Response::deny('UNAUTHORIZED');
    }

    /**
     * Invite users to a submission
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function invite(User $user, Submission $submission)
    {
        if ($submission->getEffectiveRole() == (int)Role::REVIEW_COORDINATOR_ROLE_ID) {
            return true;
        }

        return Response::deny('You do not have permission to invite users to this submission.');
    }

    /**
     * Update the inline comments of a submission
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $_
     * @param array $args
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function updateInlineComments(User $user, Submission $_, $args)
    {
        if (isset($args['inlineComments']['update'])) {
            $comment_id = $args['inlineComments']['update'][0]['id'];
            $inline_comment = InlineComment::where('id', $comment_id)->firstOrFail();
            if ($inline_comment->created_by === $user->id) {
                return true;
            }

            return Response::deny('UNAUTHORIZED');
        }

        return true;
    }

    /**
     * Update the overall comments of a submission
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $_
     * @param array $args
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function updateOverallComments(User $user, Submission $_, $args)
    {
        if (isset($args['overallComments']['update'])) {
            $comment_id = $args['overallComments']['update'][0]['id'];
            $overall_comment = OverallComment::where('id', $comment_id)->firstOrFail();
            if ($overall_comment->created_by === $user->id) {
                return true;
            }

            return Response::deny('UNAUTHORIZED');
        }

        return true;
    }
}
