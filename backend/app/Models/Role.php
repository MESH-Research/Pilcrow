<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as ParentModel;

class Role extends ParentModel
{
    use HasFactory;

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
     * Role slugs keyed by the legacy pivot role_id.
     *
     * Used by the role_id -> role (slug) data migration backfill.
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
     * Role slugs ordered most-privileged first. Backs the
     * highest-privileged-role UI hint (replaces the old min(role_id) trick).
     *
     * @var array<int, string>
     */
    public const SLUG_PRIORITY = [
        self::SLUG_APPLICATION_ADMIN,
        self::SLUG_PUBLICATION_ADMIN,
        self::SLUG_EDITOR,
        self::SLUG_REVIEW_COORDINATOR,
        self::SLUG_REVIEWER,
        self::SLUG_SUBMITTER,
    ];

    /**
     * Resolve a pivot role_id to its role slug.
     *
     * @param int|string|null $roleId
     * @return string|null
     */
    public static function slugForId($roleId): ?string
    {
        return self::ID_TO_SLUG[(string)$roleId] ?? null;
    }

    /**
     * Return the most-privileged slug from a set, or null if none rank.
     *
     * @param iterable<string> $slugs
     * @return string|null
     */
    public static function mostPrivileged(iterable $slugs): ?string
    {
        $best = null;
        $bestRank = PHP_INT_MAX;
        foreach ($slugs as $slug) {
            $rank = array_search($slug, self::SLUG_PRIORITY, true);
            if ($rank !== false && $rank < $bestRank) {
                $bestRank = $rank;
                $best = $slug;
            }
        }

        return $best;
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
