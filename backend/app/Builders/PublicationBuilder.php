<?php
declare(strict_types=1);

namespace App\Builders;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PublicationBuilder extends Builder
{
    /**
     * Scope by public visibility. Accepts a boolean so callers can filter
     * to hidden publications too (@scope passes the field arg through).
     *
     * @param bool $value
     * @return self
     */
    public function public(bool $value = true): self
    {
        return $this->where('is_publicly_visible', $value);
    }

    /**
     * Scope by accepting-submissions state. Accepts a boolean so callers
     * can filter to closed publications too (@scope passes the field arg
     * through).
     *
     * @param bool $value
     * @return self
     */
    public function acceptingSubmissions(bool $value = true): self
    {
        return $this->where('is_accepting_submissions', $value);
    }

    /**
     * Add a scope to filter publications by a search string.
     *
     * @param string $search
     * @return self
     */
    public function search(mixed $search): self
    {
        return $this->where('name', 'like', '%' . $search . '%');
    }

    /**
     * Scope to publications visible to the current user.
     *
     * @return self
     */
    public function visible(): self
    {
        $user = Auth::user();

        if ($user && $user->hasRole(Role::APPLICATION_ADMINISTRATOR)) {
            return $this;
        }

        return $this->public()->orWhereHas('users', function (Builder $query) use ($user) {
            $query->where('user_id', $user->id);
        });
    }

    /**
     * Scope to filter publications by the user's role.
     *
     * @param array $roles
     * @return self
     */
    public function myRole(array $roles): self
    {
        $user = Auth::user();

        return $this->whereHas('users', function (Builder $query) use ($user, $roles) {
            $query->where('user_id', $user->id)
                ->whereIn('role', $roles);
        });
    }
}
