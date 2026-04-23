<?php
declare(strict_types=1);

namespace App\Builders;

use App\Models\Publication;
use Illuminate\Database\Eloquent\Builder;

class PublicationAssignmentBuilder extends Builder
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
     * Search assignments by the related publication's name.
     *
     * @param string|null $search
     * @return self
     */
    public function search(?string $search): self
    {
        if ($search) {
            $this->whereHas('publication', function (Builder $query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            });
        }

        return $this;
    }

    /**
     * Restrict to assignments whose publication currently has at least
     * one non-draft submission in any of the given statuses. Used by
     * /manage to show only publications with activity in a given
     * workflow stage. Drafts are always excluded to match the
     * dashboard's status-count semantics.
     *
     * @param array|null $statuses
     * @return self
     */
    public function withStatuses(?array $statuses): self
    {
        if ($statuses) {
            $this->whereHas('publication.submissions', function (Builder $query) use ($statuses) {
                $query
                    ->whereIn('status', $statuses)
                    ->where('status', '!=', 'DRAFT');
            });
        }

        return $this;
    }

    /**
     * Order assignments by a column on the related publication.
     *
     * @param array|null $orderBy Array of {column, order} pairs
     * @return self
     */
    public function orderByPublication(?array $orderBy): self
    {
        if (!$orderBy) {
            return $this;
        }

        $columnMap = [
            'NAME' => 'name',
            'CREATED_AT' => 'created_at',
            'UPDATED_AT' => 'updated_at',
        ];

        foreach ($orderBy as $clause) {
            $column = $columnMap[$clause['column']] ?? null;
            $direction = $clause['order'] ?? 'ASC';
            if ($column) {
                $this->orderBy(
                    Publication::select($column)
                        ->whereColumn('publications.id', 'publication_user.publication_id')
                        ->limit(1),
                    $direction
                );
            }
        }

        return $this;
    }
}
