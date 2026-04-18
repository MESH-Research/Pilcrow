<?php
declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Publication;
use App\Models\Submission;
use App\Pagination\SubmissionPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaginatePublicationSubmissions
{
    /**
     * Resolve a paginated list of submissions for a publication.
     *
     * Unlike the top-level submissions query (which applies a user-visibility
     * scope), this resolver returns all submissions belonging to the publication
     * — appropriate for publication admins and editors viewing the dashboard.
     *
     * @param \App\Models\Publication $publication
     * @param array $args
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function __invoke(Publication $publication, array $args): Paginator
    {
        // Drafts are the author's private work-in-progress and are
        // intentionally hidden from the publication dashboard until the
        // author submits them for review.
        $query = $publication->submissions()
            ->where('status', '!=', Submission::DRAFT);

        // Apply search before the base-query snapshot so that
        // statusCounts also reflects the search.
        if (! empty($args['search'])) {
            $this->applySearch($query, $args['search']);
        }

        // Snapshot the base query before status filtering so that
        // statusCounts can aggregate across all statuses.
        $baseQuery = clone $query;

        // Apply status filter for the paginated data.
        // Lighthouse maps @enum values to integers before passing to the resolver.
        if (! empty($args['status'])) {
            $query->whereIn('status', $args['status']);
        }

        // Apply ordering.
        if (! empty($args['orderBy'])) {
            foreach ($args['orderBy'] as $order) {
                $column = strtolower($order['column']);
                $direction = strtolower($order['order']);
                $query->orderBy($column, $direction);
            }
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        $page = $args['page'] ?? 1;
        $first = $args['first'];

        $basePaginator = $query->paginate($first, ['*'], 'page', $page);

        $paginator = new SubmissionPaginator(
            $basePaginator->items(),
            $basePaginator->total(),
            $basePaginator->perPage(),
            $basePaginator->currentPage(),
            ['path' => $basePaginator->path()]
        );

        $paginator->setBaseQuery($baseQuery);

        return $paginator;
    }

    /** Minimum length of the search term (post-prefix). */
    private const MIN_SEARCH_LENGTH = 3;

    /**
     * Apply a search term to the submissions query.
     *
     * Prefixes narrow the search to a single field. Without a prefix,
     * the search matches against the submission title AND any assigned
     * user (submitter, reviewer, review coordinator):
     *
     *   - title:foo           - submission title only
     *   - submitter:foo       - submitter name/email/username only
     *   - reviewer:foo        - any reviewer name/email/username only
     *   - coordinator:foo     - review coordinator name/email/username only
     *   - team:foo            - any reviewer OR coordinator (review team)
     *   - user:foo            - any assigned user, any role
     *   - foo                 - title OR any assigned user
     *
     * @param \Illuminate\Database\Eloquent\Relations\HasMany $query
     * @param string $search
     * @return void
     */
    private function applySearch(HasMany $query, string $search): void
    {
        $search = trim($search);
        if ($search === '') {
            return;
        }

        [$prefix, $term] = $this->splitPrefix($search);
        // Enforce a minimum length on whichever portion will be queried.
        $lengthTarget = $prefix !== null ? $term : $search;
        if (mb_strlen($lengthTarget) < self::MIN_SEARCH_LENGTH) {
            return;
        }

        $userRelations = [
            'submitter' => 'submitters',
            'reviewer' => 'reviewers',
            'coordinator' => 'reviewCoordinators',
            'review_coordinator' => 'reviewCoordinators',
        ];

        if ($prefix === 'title') {
            $query->where('title', 'like', $this->likeValue($term));

            return;
        }

        if ($prefix === 'user') {
            $this->whereAnyUser($query, $term, array_values(array_unique($userRelations)));

            return;
        }

        if ($prefix === 'team') {
            $this->whereAnyUser($query, $term, ['reviewers', 'reviewCoordinators']);

            return;
        }

        if (isset($userRelations[$prefix])) {
            $query->whereHas(
                $userRelations[$prefix],
                fn($q) => $this->matchUserFields($q, $term)
            );

            return;
        }

        // No prefix — match title or any assigned user
        $query->where(function ($q) use ($search, $userRelations) {
            $q->where('title', 'like', $this->likeValue($search));
            $this->whereAnyUser(
                $q,
                $search,
                array_values(array_unique($userRelations)),
                'orWhereHas'
            );
        });
    }

    /**
     * Split a search string into (prefix, term).
     *
     * @param string $search
     * @return array{0: string|null, 1: string}
     */
    private function splitPrefix(string $search): array
    {
        if (! str_contains($search, ':')) {
            return [null, $search];
        }
        [$prefix, $term] = explode(':', $search, 2);

        return [strtolower(trim($prefix)), trim($term)];
    }

    /**
     * Match the term against a user's name, email, and username.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $term
     * @return void
     */
    private function matchUserFields($query, string $term): void
    {
        $value = $this->likeValue($term);
        $query->where('users.name', 'like', $value)
            ->orWhere('users.email', 'like', $value)
            ->orWhere('users.username', 'like', $value);
    }

    /**
     * Constrain the query so at least one of the given relations has
     * a user matching the term.
     *
     * @param \Illuminate\Database\Eloquent\Relations\HasMany|\Illuminate\Database\Eloquent\Builder $query
     * @param string $term
     * @param array<int, string> $relations
     * @param string $method whereHas or orWhereHas
     * @return void
     */
    private function whereAnyUser(
        $query,
        string $term,
        array $relations,
        string $method = 'whereHas'
    ): void {
        foreach ($relations as $i => $relation) {
            $m = $i === 0 ? $method : 'orWhereHas';
            $query->{$m}($relation, fn($q) => $this->matchUserFields($q, $term));
        }
    }

    /**
     * Escape a search term for use in a SQL LIKE value and wrap it
     * with % wildcards. Escapes %, _, and \ so users can't (accidentally
     * or otherwise) inject LIKE wildcards.
     *
     * @param string $term
     * @return string
     */
    private function likeValue(string $term): string
    {
        return '%' . addcslashes($term, '%_\\') . '%';
    }
}
