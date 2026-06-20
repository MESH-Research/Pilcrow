<?php
declare(strict_types=1);

namespace App\Policies;

use App\Auth\AbilityResolver;
use App\Models\InlineComment;
use App\Models\OverallComment;
use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SubmissionPolicy
{
    use HandlesAuthorization;

    /**
     * @param \App\Auth\AbilityResolver $abilities
     */
    public function __construct(private AbilityResolver $abilities)
    {
    }

    /**
     * Check if a submission can be created.
     *
     * Role-agnostic: a submission may be created whenever the target
     * publication is accepting submissions.
     *
     * @param \App\Models\User $user
     * @param array $args
     * @return bool
     */
    public function create(User $user, $args)
    {
        $publication = Publication::where('id', $args['publication_id'])->firstOrFail();

        return $publication->is_accepting_submissions;
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function updateSubmitters(User $user, Submission $submission)
    {
        return $this->abilities->allows($user, 'submission.update-submitters', $submission)
            ? true
            : Response::deny('UNAUTHORIZED');
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function updateReviewers(User $user, Submission $submission)
    {
        return $this->abilities->allows($user, 'submission.update-reviewers', $submission)
            ? true
            : Response::deny('UNAUTHORIZED');
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function updateReviewCoordinators(User $user, Submission $submission)
    {
        return $this->abilities->allows($user, 'submission.update-review-coordinators', $submission)
            ? true
            : Response::deny('UNAUTHORIZED');
    }

    /**
     * Update submission status.
     *
     * Admins and review coordinators may change the status unconditionally;
     * submitters may only do so while the submission is still a DRAFT. That
     * draft-only condition is a conditional grant in RoleAbilities, evaluated
     * by the resolver — so this method is a uniform ability check.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function updateStatus(User $user, Submission $submission)
    {
        return $this->abilities->allows($user, 'submission.update-status', $submission)
            ? true
            : Response::deny('UNAUTHORIZED');
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function updateTitle(User $user, Submission $submission)
    {
        return $this->abilities->allows($user, 'submission.update-title', $submission)
            ? true
            : Response::deny('UNAUTHORIZED');
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Submission $submission)
    {
        return $this->abilities->allows($user, 'submission.view', $submission)
            ? true
            : Response::deny('UNAUTHORIZED');
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Submission $submission)
    {
        return $this->abilities->allows($user, 'submission.update', $submission)
            ? true
            : Response::deny('UNAUTHORIZED');
    }

    /**
     * Invite users to a submission.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function invite(User $user, Submission $submission)
    {
        return $this->abilities->allows($user, 'submission.invite', $submission)
            ? true
            : Response::deny('You do not have permission to invite users to this submission.');
    }

    /**
     * Update an inline comment of a submission
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $_
     * @param array $args
     * @return \Illuminate\Auth\Access\Response|bool
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
     * Delete an inline comment of a submission
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $_
     * @param array {submission_id: string, comment_id:string}  $args
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function deleteInlineComment(User $user, Submission $_, array $args)
    {
        if (isset($args['comment_id'])) {
            $inline_comment = InlineComment::findOrFail($args['comment_id']);
            if ($inline_comment->created_by === $user->id) {
                return true;
            }
        }

        return Response::deny('UNAUTHORIZED');
    }

    /**
     * Update an overall comment of a submission
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $_
     * @param array $args
     * @return \Illuminate\Auth\Access\Response|bool
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

    /**
     * Delete an overall comment of a submission
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $_
     * @param array {submission_id: string, comment_id:string}  $args
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function deleteOverallComment(User $user, Submission $_, array $args)
    {
        if (isset($args['comment_id'])) {
            $overall_comment = OverallComment::findOrFail($args['comment_id']);
            if ($overall_comment->created_by === $user->id) {
                return true;
            }
        }

        return Response::deny('UNAUTHORIZED');
    }
}
