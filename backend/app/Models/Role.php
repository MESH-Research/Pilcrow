<?php
declare(strict_types=1);

namespace App\Models;

use Silber\Bouncer\Database\Role as ParentModel;

/**
 * The Bouncer role model — bound via Bouncer::useRoleModel() and backing the
 * GraphQL `Role` type / `User.roles` relation.
 *
 * It models genuinely GLOBAL roles only. Today that is the single
 * application-administrator role (a real Bouncer role granted everything()).
 * Scoped publication / submission roles are NOT Bouncer roles and live in code
 * as App\Auth\ScopedRole; they have no rows here and are never assigned through
 * Bouncer.
 */
class Role extends ParentModel
{
    // Human-readable title (stored as the Bouncer role `title`; surfaced as
    // GraphQL Role.name to preserve the client contract).
    public const APPLICATION_ADMINISTRATOR = 'Application Administrator';

    // Primary key id (kept for the highest_privileged_role UI hint, where
    // application_admin ranks highest at 1).
    public const APPLICATION_ADMINISTRATOR_ROLE_ID = '1';

    // Slug — the canonical role identifier, matching the GraphQL enum name.
    public const SLUG_APPLICATION_ADMIN = 'application_admin';

    /**
     * Human-readable title keyed by slug, for the global role(s) only. Used by
     * User::assignRole to accept either a slug or a display title.
     *
     * @var array<string, string>
     */
    public const SLUG_TO_TITLE = [
        self::SLUG_APPLICATION_ADMIN => self::APPLICATION_ADMINISTRATOR,
    ];
}
