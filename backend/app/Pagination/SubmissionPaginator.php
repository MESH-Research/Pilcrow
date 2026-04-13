<?php
declare(strict_types=1);

namespace App\Pagination;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Extends LengthAwarePaginator to carry the base query (pre-pagination,
 * pre-status-filter) so that paginator-level aggregate fields like
 * statusCounts can run their own GROUP BY against the same filtered set.
 *
 * @extends \Illuminate\Pagination\LengthAwarePaginator<int, \App\Models\Submission>
 */
class SubmissionPaginator extends LengthAwarePaginator
{
    protected Builder|Relation $baseQuery;

    /**
     * Store the base query builder for use by aggregate field resolvers.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $query
     * @return self
     */
    public function setBaseQuery(Builder|Relation $query): self
    {
        $this->baseQuery = $query;

        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation
     */
    public function getBaseQuery(): Builder|Relation
    {
        return $this->baseQuery;
    }
}
