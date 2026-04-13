<?php
declare(strict_types=1);

namespace App\GraphQL\Resolvers;

use App\Pagination\SubmissionPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SubmissionStatusCountsField
{
    /**
     * Resolve the statusCounts field on SubmissionPaginator.
     *
     * Uses the base query (which includes publication/role filters but NOT
     * status filters) to return counts grouped by submission status.
     *
     * @param  \App\Pagination\SubmissionPaginator  $paginator
     * @return \Illuminate\Support\Collection
     */
    public function __invoke(SubmissionPaginator $paginator): Collection
    {
        $baseQuery = $paginator->getBaseQuery();

        return $baseQuery
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(fn ($row) => [
                'status' => (int)$row->status,
                'count' => (int)$row->count,
            ]);
    }
}
