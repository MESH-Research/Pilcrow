<?php
declare(strict_types=1);

namespace App\Auth;

/**
 * The closed catalog of authorization abilities.
 *
 * Every ability the policies check is a case here — a single, greppable
 * registry rather than magic strings scattered across call sites. The backing
 * value is the legacy dotted identifier (kept so logs / any external reference
 * stay stable). Scoped roles grant a subset of these via {@see Grant}; the
 * remainder (e.g. publication.create, user.*) are only reached through the
 * application-administrator wildcard short-circuit in {@see AbilityResolver}.
 */
enum Ability: string
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
    case PublicationCreate = 'publication.create';

    case UserView = 'user.view';
    case UserViewAny = 'user.view-any';
    case UserUpdate = 'user.update';
    case UserManageBeta = 'user.manage-beta';
}
