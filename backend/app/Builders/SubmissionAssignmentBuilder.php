<?php
declare(strict_types=1);

namespace App\Builders;

use App\Models\Submission;
use Illuminate\Database\Eloquent\Builder;

class SubmissionAssignmentBuilder extends Builder
{
    /**
     * Filter assignments by role.
     *
     * @param array|null $roles
     * @return self
     */
    public function roleFilter(?array $roles): self
    {
        if ($roles) {
            $this->whereIn('role_id', $roles);
        }

        return $this;
    }

    /**
     * Filter assignments by submission status.
     *
     * @param array|null $status
     * @return self
     */
    public function statusFilter(?array $status): self
    {
        if ($status) {
            $this->whereHas('submission', function (Builder $query) use ($status) {
                $query->whereIn('status', $status);
            });
        }

        return $this;
    }

    /**
     * Filter assignments by publication.
     *
     * @param array|null $publicationIds
     * @return self
     */
    public function publicationFilter(?array $publicationIds): self
    {
        if ($publicationIds) {
            $this->whereHas('submission', function (Builder $query) use ($publicationIds) {
                $query->whereIn('publication_id', $publicationIds);
            });
        }

        return $this;
    }

    /**
     * Search assignments by submission title.
     *
     * @param string|null $search
     * @return self
     */
    public function search(?string $search): self
    {
        if ($search) {
            $this->whereHas('submission', function (Builder $query) use ($search) {
                $query->where('title', 'like', "%{$search}%");
            });
        }

        return $this;
    }

    /**
     * Order assignments by a column on the related submission.
     *
     * @param array|null $orderBy Array of {column, order} pairs
     * @return self
     */
    public function orderBySubmission(?array $orderBy): self
    {
        if (!$orderBy) {
            return $this;
        }

        $columnMap = [
            'CREATED_AT' => 'created_at',
            'UPDATED_AT' => 'updated_at',
            'TITLE' => 'title',
            'STATUS' => 'status',
        ];

        foreach ($orderBy as $clause) {
            $column = $columnMap[$clause['column']] ?? null;
            $direction = $clause['order'] ?? 'ASC';
            if ($column) {
                $this->orderBy(
                    Submission::select($column)
                        ->whereColumn('submissions.id', 'submission_user.submission_id')
                        ->limit(1),
                    $direction
                );
            }
        }

        return $this;
    }
}
