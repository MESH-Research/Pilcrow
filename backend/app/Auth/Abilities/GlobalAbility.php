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
 * The backing value is the Bouncer ability name, and it is deliberately the
 * SAME string as the generated GraphQL field — the snake_case of the case name.
 * So `AdminUserViewAny` is the Bouncer ability `admin_user_view_any` and the
 * UserAbilities field `admin_user_view_any`: one identifier, no dotted/snake
 * split to keep in sync. (Unlike {@see SubmissionAbility}, whose dotted values
 * are pinned by legacy pivot data, these global abilities are new and unseeded —
 * nothing grants them except the application administrator's everything()
 * wildcard — so the value is free to mirror the field name.)
 *
 * Cases whose name is prefixed `Admin` surface as `admin_*` flags, and the
 * client treats holding ANY `admin_*` ability as "may reach the admin area".
 * There is deliberately no single "can access admin" ability — admin visibility
 * is the union of admin capabilities, so a new global role that adds an
 * `admin_*` ability extends admin access with no client change.
 */
enum GlobalAbility: string
{
    case PublicationCreate = 'publication_create';

    case AdminUserView = 'admin_user_view';
    case AdminUserViewAny = 'admin_user_view_any';
    case AdminUserUpdate = 'admin_user_update';
    case AdminUserManageBeta = 'admin_user_manage_beta';
}
