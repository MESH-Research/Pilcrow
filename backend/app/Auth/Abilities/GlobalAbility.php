<?php
declare(strict_types=1);

namespace App\Auth\Abilities;

/**
 * The catalog of GLOBAL (application-wide) abilities — those NOT tied to a
 * particular publication / submission.
 *
 * These are checked directly against Bouncer at the call site
 * (`$user->can(GlobalAbility::Foo)`): the application administrator holds them
 * through its everything() wildcard, and they can be granted to other roles at
 * the Bouncer layer if a runtime-editable global permission scheme is added.
 * They are deliberately a separate type from {@see ScopedAbility} and never pass
 * through {@see ScopedAbilityResolver} — which only resolves publication /
 * submission scoped abilities — so Bouncer can never short-circuit a scoped
 * check.
 *
 * Naming convention — global abilities are **verb-first** (Action + Model:
 * `CreatePublication`, `ViewUser`). This is deliberately distinct from the
 * scoped enums, whose cases are action-only on a model-named enum
 * (`PublicationAbility::View`, `SubmissionAbility::UpdateStatus`). The shape of
 * the name therefore tells you which it is: `CreatePublication` is an
 * application capability and could never be mistaken for a `PublicationAbility`
 * case. The backing value remains the dotted Bouncer ability name.
 */
enum GlobalAbility: string
{
    case CreatePublication = 'publication.create';

    case ViewUser = 'user.view';
    case ViewAnyUser = 'user.view-any';
    case UpdateUser = 'user.update';
    case ManageUserBeta = 'user.manage-beta';
}
