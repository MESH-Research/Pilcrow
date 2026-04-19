<?php
declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\GraphQL\Dtos\PublicationUser as PublicationUserDto;
use App\Models\Publication;
use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PublicationUser
{
    /**
     * Resolve a single user scoped to a publication. Returns null if
     * the user has no submission-level involvement in the publication,
     * so callers can distinguish "outsider" from "member with zero
     * activity". Counts mirror the ones on `publication.users` so
     * the two resolvers stay in sync.
     *
     * @param \App\Models\Publication $publication
     * @param array $args
     * @return \App\GraphQL\Dtos\PublicationUser|null
     */
    public function __invoke(
        Publication $publication,
        array $args
    ): ?PublicationUserDto {
        $userId = (int)$args['id'];

        $row = User::query()
            ->select('users.*')
            ->selectSub(
                $this->roleCountSubquery($publication, Role::SUBMITTER_ROLE_ID),
                'as_submitter_count'
            )
            ->selectSub(
                $this->roleCountSubquery(
                    $publication,
                    Role::REVIEWER_ROLE_ID,
                    'active'
                ),
                'as_reviewer_active_count'
            )
            ->selectSub(
                $this->roleCountSubquery(
                    $publication,
                    Role::REVIEWER_ROLE_ID,
                    'completed'
                ),
                'as_reviewer_completed_count'
            )
            ->selectSub(
                $this->roleCountSubquery(
                    $publication,
                    Role::REVIEW_COORDINATOR_ROLE_ID,
                    'active'
                ),
                'as_coordinator_active_count'
            )
            ->selectSub(
                $this->roleCountSubquery(
                    $publication,
                    Role::REVIEW_COORDINATOR_ROLE_ID,
                    'completed'
                ),
                'as_coordinator_completed_count'
            )
            ->where('users.id', $userId)
            ->whereExists(function ($q) use ($publication) {
                $q->select(DB::raw(1))
                    ->from('submission_user')
                    ->join(
                        'submissions',
                        'submissions.id',
                        '=',
                        'submission_user.submission_id'
                    )
                    ->whereColumn('submission_user.user_id', 'users.id')
                    ->where('submissions.publication_id', $publication->id)
                    ->where('submissions.status', '!=', Submission::DRAFT);
            })
            ->first();

        if ($row === null) {
            return null;
        }

        return new PublicationUserDto(
            publication: $publication,
            user: $row,
            as_submitter_count: (int)$row->getAttribute('as_submitter_count'),
            as_reviewer_active_count: (int)$row->getAttribute('as_reviewer_active_count'),
            as_reviewer_completed_count: (int)$row->getAttribute('as_reviewer_completed_count'),
            as_coordinator_active_count: (int)$row->getAttribute('as_coordinator_active_count'),
            as_coordinator_completed_count: (int)$row->getAttribute('as_coordinator_completed_count'),
        );
    }

    /**
     * Scalar subquery counting distinct non-draft submissions where
     * the user holds the given role. Matches the logic used by
     * PaginatePublicationUsers so counts stay consistent.
     *
     * @param \App\Models\Publication $publication
     * @param int $roleId
     * @param string|null $phase 'active' | 'completed' | null
     * @return \Illuminate\Database\Query\Builder
     */
    private function roleCountSubquery(
        Publication $publication,
        int|string $roleId,
        ?string $phase = null
    ) {
        $completed = [
            Submission::REJECTED,
            Submission::ACCEPTED_AS_FINAL,
            Submission::ARCHIVED,
            Submission::DELETED,
        ];

        $q = DB::table('submission_user')
            ->join(
                'submissions',
                'submissions.id',
                '=',
                'submission_user.submission_id'
            )
            ->whereColumn('submission_user.user_id', 'users.id')
            ->where('submission_user.role_id', (int)$roleId)
            ->where('submissions.publication_id', $publication->id)
            ->where('submissions.status', '!=', Submission::DRAFT);

        if ($phase === 'completed') {
            $q->whereIn('submissions.status', $completed);
        } elseif ($phase === 'active') {
            $q->whereNotIn('submissions.status', $completed);
        }

        return $q->selectRaw('COUNT(DISTINCT submissions.id)');
    }
}
