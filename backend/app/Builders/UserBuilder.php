<?php
declare(strict_types=1);

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class UserBuilder extends Builder
{
    /**
     * Filter users by a search string matched against name, username, or email.
     *
     * @param string|null $search
     * @return self
     */
    public function search(?string $search): self
    {
        if (!$search) {
            return $this;
        }

        $term = '%' . $search . '%';

        return $this->where(function (Builder $query) use ($term) {
            $query->where('name', 'like', $term)
                ->orWhere('username', 'like', $term)
                ->orWhere('email', 'like', $term);
        });
    }
}
