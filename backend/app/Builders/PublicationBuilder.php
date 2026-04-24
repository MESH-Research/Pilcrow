<?php
declare(strict_types=1);

namespace App\Builders;

use App\Models\Publication;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PublicationBuilder extends Builder
{
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
     * Add a scope to filter publications by a search string.
     *
     * Short-circuits on empty / too-short terms so single-keystroke
     * searches don't scan the table. Escapes LIKE wildcards so users
     * can't (accidentally or otherwise) inject `%` / `_` / `\` into
     * the pattern. Matches the hardening applied to the submissions
     * search in commit cd1ea18d — the FULLTEXT index on `name` exists
     * to support a future MATCH...AGAINST switch.
     *
     * @param mixed $search
     * @return self
     */
    public function search(mixed $search): self
    {
        if (!is_string($search)) {
            return $this;
        }
        $term = trim($search);
        if (mb_strlen($term) < Publication::MIN_SEARCH_LENGTH) {
            return $this;
        }

        return $this->where(
            'name',
            'like',
            '%' . addcslashes($term, '%_\\') . '%'
        );
    }

    /**
     * Scope to publications visible to the current user.
     *
     * The public/assigned disjunction is wrapped in a grouped `where`
     * so subsequent filters (search, my_role, etc.) AND against the
     * whole expression rather than OR-ing around it — otherwise
     * public publications leak through regardless of downstream
     * filters.
     *
     * @return self
     */
    public function visible(): self
    {
        $user = Auth::user();

        if ($user && $user->hasRole(Role::APPLICATION_ADMINISTRATOR)) {
            return $this;
        }

        return $this->where(function (Builder $query) use ($user) {
            $query
                ->where('is_publicly_visible', true)
                ->orWhereHas('users', function (Builder $subQuery) use ($user) {
                    $subQuery->where('user_id', $user->id);
                });
        });
    }

}
