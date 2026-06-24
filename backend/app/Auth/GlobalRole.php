<?php
declare(strict_types=1);

namespace App\Auth;

/**
 * The global (application-wide) role(s), as a slug-backed type.
 *
 * NOT an Eloquent model — the role row, its id, and the assignments are owned
 * entirely by Bouncer (its Silber\Bouncer\Database\Role model and the bouncer_*
 * tables). This enum is the app-code vocabulary: a stable slug (the backing
 * value) for seeding the Bouncer role and the isApplicationAdministrator()
 * check, so string literals aren't sprinkled around.
 *
 * Scoped publication / submission roles live in {@see ScopedRole}; this is the
 * global counterpart.
 */
enum GlobalRole: string
{
    case ApplicationAdministrator = 'application_admin';

    /**
     * The role slug — the Bouncer role name and the identifier used in app
     * code. An intent-revealing alias for the backing value.
     *
     * @return string
     */
    public function toSlug(): string
    {
        return $this->value;
    }

    /**
     * Human-readable display title.
     *
     * @deprecated Titles should be resolved from the slug via i18n, not hard
     *   coded here. Retained only while the Bouncer role row carries a `title`
     *   and the legacy GraphQL `Role.name` surfaces a display name.
     * @return string
     */
    public function title(): string
    {
        return match ($this) {
            self::ApplicationAdministrator => 'Application Administrator',
        };
    }

    /**
     * The legacy integer role id for the global role, mirroring
     * {@see ScopedRole::legacyId()}. The global role lives in Bouncer (not a
     * pivot), so this is only the privilege-rank / GraphQL `UserRoles` value.
     *
     * @return int
     */
    public function legacyId(): int
    {
        return match ($this) {
            self::ApplicationAdministrator => 1,
        };
    }

    /**
     * Privilege rank for the `highest_privileged_role` UI hint (lower ranks
     * higher). The global administrator outranks every scoped role. By
     * construction this is the legacy role id — a display hint, not a Bouncer id.
     *
     * @see ScopedRole::rank()
     * @return int
     */
    public function rank(): int
    {
        return $this->legacyId();
    }
}
