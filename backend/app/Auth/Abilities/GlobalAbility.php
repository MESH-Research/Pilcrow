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
 * SAME string as the GraphQL enum value — the snake_case of the case name.
 * So `AdminUserViewAny` is the Bouncer ability `admin_user_view_any` and the
 * UserAbility enum value `admin_user_view_any`: one identifier, no dotted/snake
 * split to keep in sync. (Unlike {@see SubmissionAbility}, whose dotted values
 * are pinned by legacy pivot data, these global abilities are new and unseeded —
 * nothing grants them except the application administrator's everything()
 * wildcard — so the value is free to mirror the exposed name.)
 *
 * Cases annotated {@see Exposed} are part of the public GraphQL contract: they
 * become values of the `UserAbility` GraphQL enum and appear in the viewer's
 * granted-abilities array. Unannotated cases stay server-only.
 *
 * Cases whose name is prefixed `Admin` are admin capabilities; the client gates
 * the admin area on {@see self::AdminArea}, the derived union of them, so a new
 * global role that adds an `admin_*` ability extends admin access with no
 * client change.
 */
enum GlobalAbility: string
{
    #[Exposed('Viewer may create a publication.')]
    case PublicationCreate = 'publication_create';

    #[Exposed("Viewer may see an individual user's admin detail page.")]
    case AdminUserView = 'admin_user_view';

    #[Exposed('Viewer may browse the admin user list.')]
    case AdminUserViewAny = 'admin_user_view_any';

    #[Exposed('Viewer may edit users in the admin area.')]
    case AdminUserUpdate = 'admin_user_update';

    #[Exposed('Viewer may manage beta access and feature opt-ins for users.')]
    case AdminUserManageBeta = 'admin_user_manage_beta';

    /**
     * DERIVED, never granted directly: held when the viewer holds ANY `admin_*`
     * ability. {@see \App\Models\User::globalAbilities()} computes the union
     * instead of asking Bouncer for this case.
     */
    #[Exposed('Viewer may reach the admin area.')]
    case AdminArea = 'admin_area';
}
