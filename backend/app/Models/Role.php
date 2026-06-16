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

    /**
     * Stable Bouncer role slugs keyed by pivot role_id.
     *
     * The ABAC ability registry (Bouncer) is keyed by these slugs; pivot
     * assignments (publication_user / submission_user) reference role_id.
     * This map bridges the two.
     */
    public const ID_TO_SLUG = [
        self::APPLICATION_ADMINISTRATOR_ROLE_ID => 'application-administrator',
        self::PUBLICATION_ADMINISTRATOR_ROLE_ID => 'publication-administrator',
        self::EDITOR_ROLE_ID => 'editor',
        self::REVIEW_COORDINATOR_ROLE_ID => 'review-coordinator',
        self::REVIEWER_ROLE_ID => 'reviewer',
        self::SUBMITTER_ROLE_ID => 'submitter',
    ];

    /**
     * Resolve a pivot role_id to its Bouncer role slug.
     *
     * @param int|string|null $roleId
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
