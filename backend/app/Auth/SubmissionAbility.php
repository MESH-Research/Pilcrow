<?php
declare(strict_types=1);

namespace App\Auth;

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
    case Update = 'submission.update';
    case UpdateStatus = 'submission.update-status';
    case UpdateTitle = 'submission.update-title';
    case UpdateSubmitters = 'submission.update-submitters';
    case UpdateReviewers = 'submission.update-reviewers';
    case UpdateReviewCoordinators = 'submission.update-review-coordinators';
    case Invite = 'submission.invite';
}
