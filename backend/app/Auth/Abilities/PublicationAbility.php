<?php
declare(strict_types=1);

namespace App\Auth\Abilities;

/**
 * Scoped abilities acting on a {@see \App\Models\Publication}.
 *
 * Resolved against the publication by {@see ScopedAbilityResolver} via the
 * {@see ScopedRole} grant map. The backing value is the legacy dotted
 * identifier.
 *
 * Cases annotated {@see Exposed} are part of the public GraphQL contract: they
 * become values of the `PublicationAbility` GraphQL enum and appear in the
 * viewer's granted-abilities array on a publication. Unannotated cases stay
 * server-only.
 */
enum PublicationAbility: string implements ScopedAbility
{
    #[Exposed('Viewer may read this publication.')]
    case View = 'publication.view';

    #[Exposed('Viewer may edit this publication\'s settings, users, and content.')]
    case Update = 'publication.update';
}
