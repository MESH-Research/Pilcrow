<?php
declare(strict_types=1);

namespace App\Auth;

/**
 * The closed catalog of SCOPED abilities — those resolved against a
 * publication / submission by {@see ScopedAbilityResolver} via the {@see ScopedRole}
 * grant map.
 *
 * Deliberately scoped-only: global, application-wide abilities (creating a
 * publication, managing users, etc.) are NOT here. They are app-admin-only
 * today (checked with a plain isApplicationAdministrator() role test) and, if a
 * runtime-editable global layer is ever added, belong to Bouncer — never to this
 * enum or the resolver. Keeping them out means seeing a ScopedAbility case is
 * proof the resolver handles it, and Bouncer can never short-circuit a scoped
 * check (only the app-admin role does, explicitly).
 *
 * The backing value is the legacy dotted identifier.
 */
enum ScopedAbility: string
{
    case SubmissionView = 'submission.view';
    case SubmissionUpdate = 'submission.update';
    case SubmissionUpdateStatus = 'submission.update-status';
    case SubmissionUpdateTitle = 'submission.update-title';
    case SubmissionUpdateSubmitters = 'submission.update-submitters';
    case SubmissionUpdateReviewers = 'submission.update-reviewers';
    case SubmissionUpdateReviewCoordinators = 'submission.update-review-coordinators';
    case SubmissionInvite = 'submission.invite';

    case PublicationView = 'publication.view';
    case PublicationUpdate = 'publication.update';
}
