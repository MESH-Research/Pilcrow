<?php
declare(strict_types=1);

namespace App\Auth\Abilities;

/**
 * Scoped abilities acting on a {@see \App\Models\Publication}.
 *
 * Resolved against the publication by {@see ScopedAbilityResolver} via the
 * {@see ScopedRole} grant map. The backing value is the legacy dotted
 * identifier.
 */
enum PublicationAbility: string implements ScopedAbility
{
    case View = 'publication.view';
    case Update = 'publication.update';
}
