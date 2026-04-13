<?php
declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Publication;
use App\Pagination\SubmissionPaginator;
use Illuminate\Contracts\Pagination\Paginator;

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
        $query = $publication->submissions();

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
}
