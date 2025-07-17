<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PublicationBuilder extends Builder
{
    /**
     * Scope only publically visible publications.
     *
     * @return self
     */
    public function isPubliclyVisible()
    {
        return $this->where('is_publicly_visible', true);
    }


    /**
     * Scope only public publications.
     *
     * @return self
     */
    public function public(): self
    {
        return $this->where('is_publicly_visible', true);
    }

    /**
     * Scope only to publications that are accepting submissions.
     *
     * @return self
     */
    public function acceptingSubmissions(): self
    {
        return $this->where('is_accepting_submissions', true);
    }

    /**
     * Scope only publications that are accepting submissions
     *
     * @return self
     */
    public function isAcceptingSubmissions()
    {
        return $this->where('is_accepting_submissions', true);
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

    public function visible(): self
    {
        $user = Auth::user();
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
