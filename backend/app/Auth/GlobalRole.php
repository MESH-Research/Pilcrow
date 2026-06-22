<?php
declare(strict_types=1);

namespace App\Auth;

/**
 * Vocabulary for the global (application-wide) role(s).
 *
 * This is NOT an Eloquent model — it is a small constants holder. The actual
 * role row, its id, and the assignments are owned entirely by Bouncer (its own
 * Silber\Bouncer\Database\Role model and the bouncer_* tables). All we need in
 * app code is a stable place to name the role slug (and its display title) so
 * seeding and the `isApplicationAdministrator()` role check don't sprinkle
 * string literals around.
 *
 * Scoped publication / submission roles live in {@see ScopedRole}; this is the
 * global counterpart.
 */
final class GlobalRole
{
    public const SLUG_APPLICATION_ADMIN = 'application_admin';
    public const APPLICATION_ADMINISTRATOR = 'Application Administrator';

    /**
     * Display title keyed by slug, for seeding the Bouncer role and for
     * User::assignRole accepting either a slug or a title.
     *
     * @var array<string, string>
     */
    public const SLUG_TO_TITLE = [
        self::SLUG_APPLICATION_ADMIN => self::APPLICATION_ADMINISTRATOR,
    ];

    /**
     * The value the `highest_privileged_role` UI hint reports for an
     * application administrator. This is the GraphQL `UserRoles` enum value for
     * `application_admin` (the client contract), where lower ranks higher — it
     * is not a Bouncer id.
     */
    public const PRIVILEGE_RANK = 1;
}
