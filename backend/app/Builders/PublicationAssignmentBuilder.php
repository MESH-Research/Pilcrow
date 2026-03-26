<?php
declare(strict_types=1);

namespace App\Builders;

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
}
