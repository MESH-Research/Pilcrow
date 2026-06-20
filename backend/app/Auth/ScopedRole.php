<?php
declare(strict_types=1);

namespace App\Auth;

/**
 * The scoped (publication / submission) roles.
 *
 * These are deliberately NOT Bouncer roles and have no rows in bouncer_roles.
 * A user holds a scoped role per-entity through the publication_user /
 * submission_user pivots (the integer role_id column); AbilityResolver maps
 * that role_id to a slug and looks the grant up in the code-owned
 * RoleAbilities matrix. Nothing here is stored in, seeded into, or assignable
 * through Bouncer — that is reserved for genuinely global roles
 * (App\Models\Role, e.g. application_admin).
 *
 * This is a static catalog of role identity (id / slug / title), kept separate
 * from the Bouncer role model so the two kinds of role are not conflated.
 */
final class ScopedRole
{
    // Human-readable titles (surfaced as GraphQL Role.name where a scoped role
    // is exposed by title).
    public const PUBLICATION_ADMINISTRATOR = 'Publication Administrator';
    public const EDITOR = 'Editor';
    public const REVIEW_COORDINATOR = 'Review Coordinator';
    public const REVIEWER = 'Reviewer';
    public const SUBMITTER = 'Submitter';

    // Pivot role_id values (strings, matching the historical role_id typing).
    public const PUBLICATION_ADMINISTRATOR_ROLE_ID = '2';
    public const EDITOR_ROLE_ID = '3';
    public const REVIEW_COORDINATOR_ROLE_ID = '4';
    public const REVIEWER_ROLE_ID = '5';
    public const SUBMITTER_ROLE_ID = '6';

    // Slugs — the vocabulary the ability matrix is keyed by, matching the
    // GraphQL enum names.
    public const SLUG_PUBLICATION_ADMIN = 'publication_admin';
    public const SLUG_EDITOR = 'editor';
    public const SLUG_REVIEW_COORDINATOR = 'review_coordinator';
    public const SLUG_REVIEWER = 'reviewer';
    public const SLUG_SUBMITTER = 'submitter';

    /**
     * Scoped slugs keyed by the integer pivot role_id.
     *
     * The pivots store role_id; AbilityResolver maps it through this to the
     * slug the ability matrix is keyed by. (Replacing role_id with the slug
     * column directly on the pivots is a deferred follow-on.)
     *
     * @var array<string, string>
     */
    public const ID_TO_SLUG = [
        self::PUBLICATION_ADMINISTRATOR_ROLE_ID => self::SLUG_PUBLICATION_ADMIN,
        self::EDITOR_ROLE_ID => self::SLUG_EDITOR,
        self::REVIEW_COORDINATOR_ROLE_ID => self::SLUG_REVIEW_COORDINATOR,
        self::REVIEWER_ROLE_ID => self::SLUG_REVIEWER,
        self::SUBMITTER_ROLE_ID => self::SLUG_SUBMITTER,
    ];

    /**
     * Resolve an integer pivot role_id to its scoped role slug (the vocabulary
     * the ability matrix is keyed by). Returns null for an unknown id.
     *
     * @param string|int|null $roleId
     * @return string|null
     */
    public static function slugForId($roleId): ?string
    {
        return self::ID_TO_SLUG[(string)$roleId] ?? null;
    }
}
