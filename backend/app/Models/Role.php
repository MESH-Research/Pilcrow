<?php
declare(strict_types=1);

namespace App\Models;

use Silber\Bouncer\Database\Role as ParentModel;

class Role extends ParentModel
{
    // Human-readable role titles (stored as the Bouncer role `title`; surfaced
    // as GraphQL Role.name to preserve the client contract).

    // Relative to the application
    public const APPLICATION_ADMINISTRATOR = 'Application Administrator';

    // Relative to publications
    public const PUBLICATION_ADMINISTRATOR = 'Publication Administrator';
    public const EDITOR = 'Editor';

    // Relative to submissions
    public const REVIEW_COORDINATOR = 'Review Coordinator';
    public const REVIEWER = 'Reviewer';
    public const SUBMITTER = 'Submitter';

    // Primary Key IDs
    public const APPLICATION_ADMINISTRATOR_ROLE_ID = '1';
    public const PUBLICATION_ADMINISTRATOR_ROLE_ID = '2';
    public const EDITOR_ROLE_ID = '3';
    public const REVIEW_COORDINATOR_ROLE_ID = '4';
    public const REVIEWER_ROLE_ID = '5';
    public const SUBMITTER_ROLE_ID = '6';

    // Role slugs — the canonical role identifier used by the pivot `role`
    // column, the Bouncer ability registry, and the GraphQL enums. These match
    // the GraphQL enum names so a single vocabulary spans storage and API.
    public const SLUG_APPLICATION_ADMIN = 'application_admin';
    public const SLUG_PUBLICATION_ADMIN = 'publication_admin';
    public const SLUG_EDITOR = 'editor';
    public const SLUG_REVIEW_COORDINATOR = 'review_coordinator';
    public const SLUG_REVIEWER = 'reviewer';
    public const SLUG_SUBMITTER = 'submitter';

    /**
     * Role slugs keyed by the integer pivot role_id.
     *
     * The pivots still store role_id; AbilityResolver maps it through this to
     * the slug the ability matrix is keyed by. (Replacing role_id with the slug
     * column is a deferred follow-on; until then this is a live lookup.)
     */
    public const ID_TO_SLUG = [
        self::APPLICATION_ADMINISTRATOR_ROLE_ID => self::SLUG_APPLICATION_ADMIN,
        self::PUBLICATION_ADMINISTRATOR_ROLE_ID => self::SLUG_PUBLICATION_ADMIN,
        self::EDITOR_ROLE_ID => self::SLUG_EDITOR,
        self::REVIEW_COORDINATOR_ROLE_ID => self::SLUG_REVIEW_COORDINATOR,
        self::REVIEWER_ROLE_ID => self::SLUG_REVIEWER,
        self::SUBMITTER_ROLE_ID => self::SLUG_SUBMITTER,
    ];

    /**
     * Human-readable title for each role slug, used as the Bouncer role title.
     *
     * @var array<string, string>
     */
    public const SLUG_TO_TITLE = [
        self::SLUG_APPLICATION_ADMIN => self::APPLICATION_ADMINISTRATOR,
        self::SLUG_PUBLICATION_ADMIN => self::PUBLICATION_ADMINISTRATOR,
        self::SLUG_EDITOR => self::EDITOR,
        self::SLUG_REVIEW_COORDINATOR => self::REVIEW_COORDINATOR,
        self::SLUG_REVIEWER => self::REVIEWER,
        self::SLUG_SUBMITTER => self::SUBMITTER,
    ];

    /**
     * Resolve an integer pivot role_id to its role slug (the vocabulary the
     * ability matrix is keyed by).
     *
     * @param string|int|null $roleId
     * @return string|null
     */
    public static function slugForId($roleId): ?string
    {
        return self::ID_TO_SLUG[(string)$roleId] ?? null;
    }

    /**
     * @return array
     */
    public static function getArrayOfAllRoleNames()
    {
        return [
            Role::APPLICATION_ADMINISTRATOR,
            Role::PUBLICATION_ADMINISTRATOR,
            Role::EDITOR,
            Role::REVIEW_COORDINATOR,
            Role::REVIEWER,
            Role::SUBMITTER,
        ];
    }
}
