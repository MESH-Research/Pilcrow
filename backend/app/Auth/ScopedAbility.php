<?php
declare(strict_types=1);

namespace App\Auth;

/**
 * Marker type for the closed catalog of SCOPED abilities — those resolved
 * against a publication / submission by {@see ScopedAbilityResolver} via the
 * {@see ScopedRole} grant map.
 *
 * The cases live on two enums that implement this interface, split by the model
 * they act on purely for organization: {@see SubmissionAbility} and
 * {@see PublicationAbility}. The interface is the common type used wherever a
 * scoped ability is accepted ({@see Grant}, {@see ScopedRole::allows},
 * {@see ScopedAbilityResolver::allows}), so either enum's case is a valid
 * argument while a {@see GlobalAbility} (or a string) is a type error.
 *
 * Deliberately scoped-only: global, application-wide abilities (creating a
 * publication, managing users, etc.) are NOT here — they are {@see GlobalAbility}
 * cases, checked directly against Bouncer with `$user->can(...)`. Keeping them
 * out means seeing a scoped ability is proof the resolver handles it, and
 * Bouncer can never short-circuit a scoped check (only the app-admin role does,
 * explicitly).
 */
interface ScopedAbility
{
}
