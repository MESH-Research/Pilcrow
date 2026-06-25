<?php
declare(strict_types=1);

namespace App\Builders;

use App\Auth\Abilities\PublicationAbility;
use App\Auth\Abilities\ScopedAbility;
use App\Auth\Roles\ScopedRole;
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
    public function search(?string $search): self
    {
        if (!$search) {
            return $this;
        }

        return $this->where('name', 'like', '%' . $search . '%');
    }

    /**
     * Scope to publications visible to the current user.
     *
     * Guests see only publicly visible publications. Application
     * administrators see everything. Other authenticated users see publicly
     * visible publications plus any private publication they hold a role on.
     *
     * @return self
     */
    public function visible(): self
    {
        $user = Auth::user();

        if ($user && $user->isApplicationAdministrator()) {
            return $this;
        }

        // Membership branch derives from the same matrix the resolver uses:
        // the publication-pivot roles that grant publication.view.
        $slugs = ScopedRole::grantingSlugsFor(PublicationAbility::View, ScopedRole::PIVOT_PUBLICATION);

        return $this->where(function (Builder $query) use ($user, $slugs) {
            $query->where('is_publicly_visible', true);
            if ($user && $slugs !== []) {
                $query->orWhereHas('users', function (Builder $sub) use ($user, $slugs) {
                    $sub->where('user_id', $user->id)->whereIn('role', $slugs);
                });
            }
        });
    }

    /**
     * Scope to publications on which the current user is granted a scoped
     * ability, resolved from the {@see ScopedRole} matrix (the same source as
     * {@see \App\Auth\ScopedAbilityResolver}) so list-filtering and item
     * authorization cannot diverge. Application administrators match everything;
     * a guest or a user with no granting role matches nothing.
     *
     * @param \App\Auth\Abilities\ScopedAbility $ability
     * @return self
     */
    public function whereCan(ScopedAbility $ability): self
    {
        $user = Auth::user();

        if ($user && $user->isApplicationAdministrator()) {
            return $this;
        }

        $slugs = ScopedRole::grantingSlugsFor($ability, ScopedRole::PIVOT_PUBLICATION);

        if (!$user || $slugs === []) {
            return $this->whereRaw('1 = 0');
        }

        return $this->whereHas('users', function (Builder $query) use ($user, $slugs) {
            $query->where('user_id', $user->id)->whereIn('role', $slugs);
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

        if (!$user) {
            return $this->whereRaw('1 = 0');
        }

        return $this->whereHas('users', function (Builder $query) use ($user, $roles) {
            $query->where('user_id', $user->id)
                ->whereIn('role', $roles);
        });
    }
}
