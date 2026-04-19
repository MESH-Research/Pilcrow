<?php
declare(strict_types=1);

namespace App\GraphQL\Dtos;

use App\Models\Publication;
use App\Models\User;

/**
 * DTO backing the GraphQL PublicationUser type. Carries the user
 * plus per-role submission counts for this publication, and keeps
 * a reference to the publication so the `submissions` field
 * resolver can scope its query.
 */
class PublicationUser
{
    public readonly string $id;

    /**
     * @param \App\Models\Publication $publication
     * @param \App\Models\User $user
     * @param int $as_submitter_count
     * @param int $as_reviewer_active_count
     * @param int $as_reviewer_completed_count
     * @param int $as_coordinator_active_count
     * @param int $as_coordinator_completed_count
     */
    public function __construct(
        public readonly Publication $publication,
        public readonly User $user,
        public readonly int $as_submitter_count,
        public readonly int $as_reviewer_active_count,
        public readonly int $as_reviewer_completed_count,
        public readonly int $as_coordinator_active_count,
        public readonly int $as_coordinator_completed_count,
    ) {
        $this->id = (string)$user->id;
    }
}
