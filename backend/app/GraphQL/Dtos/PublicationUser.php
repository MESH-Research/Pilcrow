<?php
declare(strict_types=1);

namespace App\GraphQL\Dtos;

use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

    /**
     * Submission counts grouped by status for the submissions this
     * user is attached to within this publication. When `$roles`
     * is provided, counts are limited to submissions where the
     * user holds one of those roles — e.g. [reviewer,
     * review_coordinator] for the team member view, [submitter]
     * for the submitter view. Drafts are excluded to match the
     * rest of the dashboard.
     *
     * @param array<int,int>|null $roles
     * @return \Illuminate\Support\Collection<int, array{status: int, count: int}>
     */
    public function getSubmissionStatusCounts(?array $roles = null): Collection
    {
        $query = DB::table('submissions')
            ->join(
                'submission_user',
                'submission_user.submission_id',
                '=',
                'submissions.id'
            )
            ->where('submissions.publication_id', $this->publication->id)
            ->where('submission_user.user_id', $this->user->id)
            ->where('submissions.status', '!=', Submission::DRAFT);

        if (! empty($roles)) {
            $query->whereIn(
                'submission_user.role_id',
                array_map('intval', $roles)
            );
        }

        return $query
            ->groupBy('submissions.status')
            ->select(
                'submissions.status',
                DB::raw('COUNT(DISTINCT submissions.id) as count')
            )
            ->get()
            ->map(fn($row) => [
                'status' => (int)$row->status,
                'count' => (int)$row->count,
            ]);
    }
}
