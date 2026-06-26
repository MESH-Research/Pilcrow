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
 * The backing value is the Bouncer ability name. The CASE name is what the
 * GraphQL UserAbilities field is generated from (snake_case): cases whose name
 * is prefixed `Admin` surface as `admin_*` flags, and the client treats holding
 * ANY `admin_*` ability as "may reach the admin area". There is deliberately no
 * single "can access admin" ability — admin visibility is the union of admin
 * capabilities, so a new global role that adds an `admin_*` ability extends
 * admin access with no client change.
 */
enum GlobalAbility: string
{
    case PublicationCreate = 'publication.create';

    case AdminUserView = 'user.view';
    case AdminUserViewAny = 'user.view-any';
    case AdminUserUpdate = 'user.update';
    case AdminUserManageBeta = 'user.manage-beta';
}
