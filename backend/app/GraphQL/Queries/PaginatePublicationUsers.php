<?php
declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\GraphQL\Dtos\PublicationUser as PublicationUserDto;
use App\Models\Publication;
use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PaginatePublicationUsers
{
    /** Minimum length of the search term. */
    private const MIN_SEARCH_LENGTH = 3;

    /**
     * Return a paginated list of users who hold at least one of the
     * requested submission-level roles on a non-draft submission in
     * the publication.
     *
     * Users are deduplicated (one row per user) regardless of how many
     * submissions they appear on. Each row carries scalar subquery
     * counts for the three submission roles so callers can show a
     * workload summary without N+1 queries.
     *
     * @param \App\Models\Publication $publication
     * @param array $args
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function __invoke(Publication $publication, array $args): Paginator
    {
        // Reviewer anonymity + team privacy: only the publication's
        // admin team (or an app admin) can enumerate users.
        $publication->requireManage();

        $roles = $args['roles'] ?? [];
        $roles = array_map('intval', $roles);
        // null/missing = no filter; true = only staged; false = only non-staged.
        $stagedFilter = array_key_exists('staged', $args) && $args['staged'] !== null
            ? (bool)$args['staged']
            : null;

        $query = User::query()
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
            ->whereExists(function ($q) use ($publication, $roles) {
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
                if (! empty($roles)) {
                    $q->whereIn('submission_user.role_id', $roles);
                }
            });

        if ($stagedFilter === true) {
            $query->where('users.staged', true);
        } elseif ($stagedFilter === false) {
            $query->whereNull('users.staged');
        }

        if (! empty($args['search'])) {
            $this->applySearch($query, $args['search']);
        }

        if (! empty($args['orderBy'])) {
            foreach ($args['orderBy'] as $order) {
                $query->orderBy(
                    $this->orderColumn($order['column']),
                    strtolower($order['order'])
                );
            }
        } else {
            $query->orderBy('users.name');
        }

        $page = $args['page'] ?? 1;
        $first = $args['first'];

        $users = $query->paginate($first, ['*'], 'page', $page);

        // Wrap each user in a PublicationUser DTO so the nested
        // `submissions` resolver has the publication context without
        // us having to smuggle it through the User model.
        $items = $users->getCollection()->map(
            fn(User $u) => new PublicationUserDto(
                publication: $publication,
                user: $u,
                as_submitter_count: (int)$u->getAttribute('as_submitter_count'),
                as_reviewer_active_count: (int)$u->getAttribute(
                    'as_reviewer_active_count'
                ),
                as_reviewer_completed_count: (int)$u->getAttribute(
                    'as_reviewer_completed_count'
                ),
                as_coordinator_active_count: (int)$u->getAttribute(
                    'as_coordinator_active_count'
                ),
                as_coordinator_completed_count: (int)$u->getAttribute(
                    'as_coordinator_completed_count'
                ),
            )
        );

        return new LengthAwarePaginator(
            $items,
            $users->total(),
            $users->perPage(),
            $users->currentPage(),
            [
                'path' => $users->path(),
                'pageName' => $users->getPageName(),
            ]
        );
    }

    /**
     * Scalar subquery returning the number of distinct submissions in
     * this publication where the user holds the given role (excluding
     * drafts).
     *
     * The optional $phase further scopes to active (in-progress) or
     * completed submissions; omit to count all non-draft statuses.
     * "Completed" = REJECTED / ACCEPTED_AS_FINAL / ARCHIVED / DELETED,
     * which matches the dashboard's Completed category.
     *
     * @param \App\Models\Publication $publication
     * @param string|int $roleId
     * @param string|null $phase 'active' | 'completed' | null
     * @return \Illuminate\Database\Query\Builder
     */
    private function roleCountSubquery(
        Publication $publication,
        $roleId,
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

    /**
     * Apply a search term to the users query. Matches name, email,
     * and username with wildcards escaped.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return void
     */
    private function applySearch($query, string $search): void
    {
        $search = trim($search);
        if (mb_strlen($search) < self::MIN_SEARCH_LENGTH) {
            return;
        }
        $value = '%' . addcslashes($search, '%_\\') . '%';
        $query->where(function ($q) use ($value) {
            $q->where('users.name', 'like', $value)
                ->orWhere('users.email', 'like', $value)
                ->orWhere('users.username', 'like', $value);
        });
    }

    /**
     * Map allowed orderBy columns from the GraphQL enum to SQL.
     * Role-count columns reference the selectSub aliases.
     *
     * @param string $column
     * @return string
     */
    private function orderColumn(string $column): string
    {
        return match (strtoupper($column)) {
            'AS_SUBMITTER_COUNT' => 'as_submitter_count',
            'AS_REVIEWER_ACTIVE_COUNT' => 'as_reviewer_active_count',
            'AS_REVIEWER_COMPLETED_COUNT' => 'as_reviewer_completed_count',
            'AS_COORDINATOR_ACTIVE_COUNT' => 'as_coordinator_active_count',
            'AS_COORDINATOR_COMPLETED_COUNT' => 'as_coordinator_completed_count',
            'EMAIL' => 'users.email',
            'USERNAME' => 'users.username',
            default => 'users.name',
        };
    }
}
