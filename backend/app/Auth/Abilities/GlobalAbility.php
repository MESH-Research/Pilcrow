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
 * The backing value is the Bouncer ability name.
 */
enum GlobalAbility: string
{
    case PublicationCreate = 'publication.create';

    case UserView = 'user.view';
    case UserViewAny = 'user.view-any';
    case UserUpdate = 'user.update';
    case UserManageBeta = 'user.manage-beta';

    case AdminAvatarModerate = 'admin_avatar_moderate';
}
