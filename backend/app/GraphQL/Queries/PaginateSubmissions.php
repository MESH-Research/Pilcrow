<?php
declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Submission;
use App\Pagination\SubmissionPaginator;
use Illuminate\Contracts\Pagination\Paginator;

class PaginateSubmissions
{
    /**
     * Custom resolver for the paginated submissions query.
     *
     * Replicates the scopes that were previously declared via @scope
     * directives but stores the base query (before pagination and before
     * status filtering) on a SubmissionPaginator so that aggregate fields
     * like statusCounts can derive their values from the same query context.
     *
     * @param mixed $root
     * @param array $args
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function __invoke($root, array $args): Paginator
    {
        $query = Submission::query()->visible();

        // Apply filters that define the "base" context (publication, role).
        if (! empty($args['publication'])) {
            $query->publicationFilter($args['publication']);
        }

        if (! empty($args['my_roles'])) {
            $query->myRoleFilter($args['my_roles']);
        }

        // Snapshot the base query before status filtering so that
        // statusCounts can aggregate across all statuses.
        $baseQuery = clone $query;

        // Now apply status filter for the paginated data.
        if (! empty($args['status'])) {
            $query->statusFilter($args['status']);
        }

        // Apply ordering.
        if (! empty($args['orderBy'])) {
            foreach ($args['orderBy'] as $order) {
                $column = strtolower($order['column']);
                $direction = strtolower($order['order']);
                $query->orderBy($column, $direction);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $page = $args['page'] ?? 1;
        $first = $args['first'];

        // Paginate and wrap in our custom paginator.
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
}
