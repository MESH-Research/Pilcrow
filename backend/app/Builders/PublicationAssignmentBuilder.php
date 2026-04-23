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
