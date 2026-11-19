<?php
declare(strict_types=1);

namespace App\Auth\Abilities;

/**
 * Scoped abilities acting on a {@see \App\Models\Submission}.
 *
 * Resolved against the submission (and its parent publication's admin roles) by
 * {@see ScopedAbilityResolver} via the {@see ScopedRole} grant map. The backing
 * value is the legacy dotted identifier.
 *
 * Cases annotated {@see Exposed} are part of the public GraphQL contract: they
 * become values of the `SubmissionAbility` GraphQL enum and appear in the
 * viewer's granted-abilities array on a submission. Unannotated cases stay
 * server-only.
 */
enum SubmissionAbility: string implements ScopedAbility
{
    #[Exposed('Viewer may read this submission.')]
    case View = 'submission.view';

    /**
     * Edit the work itself — body, file, and title, as one capability — held by a
     * submitter only while the submission is DRAFT (authors own content). The
     * manuscript-content mutations, including the title edit, gate on this.
     */
    #[Exposed('Viewer may edit the manuscript — body, file, and title.')]
    case UpdateContent = 'submission.update-content';

    /**
     * Access the manuscript and post comments, held by reviewers (and inherited
     * up the role chain) only while the submission is reviewable (UNDER_REVIEW).
     */
    #[Exposed('Viewer may access the manuscript and post comments.')]
    case Review = 'submission.review';

    /**
     * Send a DRAFT in for review — the submitter's one forward action, separate
     * from the editorial status workflow ({@see self::UpdateStatus}). Held by a
     * submitter only while DRAFT.
     */
    #[Exposed('Viewer may send this submission in for review.')]
    case Submit = 'submission.submit';

    /**
     * Move a submission through the editorial status workflow, in any state.
     * Held by review coordinators and the roles above them.
     */
    #[Exposed('Viewer may change this submission\'s status.')]
    case UpdateStatus = 'submission.update-status';

    #[Exposed('Viewer may add or remove submitters.')]
    case UpdateSubmitters = 'submission.update-submitters';

    #[Exposed('Viewer may assign or unassign reviewers.')]
    case UpdateReviewers = 'submission.update-reviewers';

    #[Exposed('Viewer may assign or unassign review coordinators.')]
    case UpdateReviewCoordinators = 'submission.update-review-coordinators';
}
