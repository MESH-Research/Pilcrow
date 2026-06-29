<?php
declare(strict_types=1);

namespace App\Auth\Abilities;

/**
 * Scoped abilities acting on a {@see \App\Models\Submission}.
 *
 * Resolved against the submission (and its parent publication's admin roles) by
 * {@see ScopedAbilityResolver} via the {@see ScopedRole} grant map. The backing
 * value is the legacy dotted identifier.
 */
enum SubmissionAbility: string implements ScopedAbility
{
    case View = 'submission.view';

    /**
     * Edit the work itself — body, file, and title, as one capability — held by a
     * submitter only while the submission is DRAFT (authors own content). The
     * manuscript-content mutations (and the title) gate on this. Folds in the
     * former UpdateTitle (title is content).
     */
    case UpdateContent = 'submission.update-content';

    /**
     * Access the manuscript and post comments, held by reviewers (and inherited
     * up the role chain) only while the submission is reviewable (UNDER_REVIEW).
     */
    case Review = 'submission.review';

    /**
     * Send a DRAFT in for review — the submitter's one forward action, distinct
     * from the editorial UpdateStatus state machine. Held by a submitter only
     * while DRAFT.
     */
    case Submit = 'submission.submit';

    case UpdateStatus = 'submission.update-status';

    /**
     * The umbrella gate of the DEPRECATED `updateSubmission` god-mutation. This
     * preserves the prior broad `Update` grant (every submission role, any
     * status) so the god-mutation stays callable while clients migrate to the
     * intent-shaped mutations. Nothing else should gate on it. Removed together
     * with `updateSubmission` once the migration is complete.
     *
     * @deprecated Transitional. Use {@see self::UpdateContent},
     *   {@see self::Submit}, {@see self::UpdateStatus}, {@see self::Review}, etc.
     */
    case LegacyUpdate = 'submission.legacy-update';

    case UpdateSubmitters = 'submission.update-submitters';
    case UpdateReviewers = 'submission.update-reviewers';
    case UpdateReviewCoordinators = 'submission.update-review-coordinators';
    case Invite = 'submission.invite';
}
