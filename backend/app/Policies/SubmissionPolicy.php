<?php
declare(strict_types=1);

namespace App\Policies;

use App\Auth\Abilities\SubmissionAbility;
use App\Auth\ScopedAbilityResolver;
use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SubmissionPolicy
{
    use HandlesAuthorization;

    /**
     * @param \App\Auth\ScopedAbilityResolver $scoped
     */
    public function __construct(private ScopedAbilityResolver $scoped)
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
        return $this->scoped->allows($user, SubmissionAbility::UpdateSubmitters, $submission)
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
        return $this->scoped->allows($user, SubmissionAbility::UpdateReviewers, $submission)
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
        return $this->scoped->allows($user, SubmissionAbility::UpdateReviewCoordinators, $submission)
            ? true
            : Response::deny('UNAUTHORIZED');
    }

    /**
     * Update submission status.
     *
     * Admins and review coordinators may change the status unconditionally;
     * submitters may only do so while the submission is still a DRAFT. That
     * draft-only condition is a conditional grant in ScopedRole, evaluated
     * by the resolver — so this method is a uniform ability check.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function updateStatus(User $user, Submission $submission)
    {
        return $this->scoped->allows($user, SubmissionAbility::UpdateStatus, $submission)
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
        return $this->scoped->allows($user, SubmissionAbility::View, $submission)
            ? true
            : Response::deny('UNAUTHORIZED');
    }

    /**
     * Edit the work itself — body, file, and title as one capability —
     * author-only, draft-only.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function updateContent(User $user, Submission $submission)
    {
        return $this->scoped->allows($user, SubmissionAbility::UpdateContent, $submission)
            ? true
            : Response::deny('UNAUTHORIZED');
    }

    /**
     * Access the manuscript and post comments — held by reviewers (and up the
     * chain) only while the submission is reviewable. Gates the comment-create
     * mutations; folds in the former SubmissionIsReviewable validation rule.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function review(User $user, Submission $submission)
    {
        return $this->scoped->allows($user, SubmissionAbility::Review, $submission)
            ? true
            : Response::deny('UNAUTHORIZED');
    }

    /**
     * Send a DRAFT in for review — the submitter's forward action, draft-only.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Submission $submission
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function submit(User $user, Submission $submission)
    {
        return $this->scoped->allows($user, SubmissionAbility::Submit, $submission)
            ? true
            : Response::deny('UNAUTHORIZED');
    }
}
