<?php
declare(strict_types=1);

namespace App\Builders;

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
}
