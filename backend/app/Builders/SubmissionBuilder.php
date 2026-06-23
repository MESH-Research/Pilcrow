<?php
declare(strict_types=1);

namespace App\Builders;

use App\Auth\ScopedAbility;
use App\Auth\ScopedRole;
use App\Auth\SubmissionAbility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SubmissionBuilder extends Builder
{
    /**
     * Filter submissions by the roles of the authenticated user.
     *
     * @param array|null $roles
     * @return self
     */
    public function myRoleFilter(?array $roles): self
    {
        $user = Auth::user();

        return $this->whereHas('submissionAssignments', function ($query) use ($roles, $user) {
            $query
                ->where('user_id', $user->id)
                ->whereIn('role', $roles);
        });
    }

    /**
     * Filter submissions by the roles of any user assigned to the submission.
     *
     * This scope is a useful addition when submissions is being queried as a relationship.
     *
     * @param array|null $roles
     * @return self
     */
    public function roleFilter(?array $roles): self
    {
        return $this->whereHas('submissionAssignments', function ($query) use ($roles) {
            $query->whereIn('role', $roles);
        });
    }

    /**
     * Filter submissions by the status of the submission
     *
     * @param array|null $status
     * @return self
     */
    public function statusFilter(?array $status): self
    {
        return $this->whereIn('status', $status);
    }

    /**
     * Filter submissions by a publication id.
     *
     * @param array|null $publicationIds
     * @return self
     */
    public function publicationFilter(?array $publicationIds): self
    {
        return $this->whereIn('publication_id', $publicationIds);
    }

    /**
     * Filter submissions to only those that the user should be able to view.
     *
     * @return self
     */
    public function visible(): self
    {
        return $this->whereCan(SubmissionAbility::View);
    }

    /**
     * Scope to submissions on which the current user is granted a scoped
     * ability, resolved from the {@see ScopedRole} matrix — the same source as
     * {@see \App\Auth\ScopedAbilityResolver}, including the parent-publication
     * role inheritance — so list-filtering and item authorization cannot
     * diverge. Application administrators match everything; a guest or a user
     * with no granting role matches nothing.
     *
     * @param \App\Auth\ScopedAbility $ability
     * @return self
     */
    public function whereCan(ScopedAbility $ability): self
    {
        $user = Auth::user();

        if ($user && $user->isApplicationAdministrator()) {
            return $this;
        }

        $submissionSlugs = ScopedRole::grantingSlugsFor($ability, ScopedRole::PIVOT_SUBMISSION);
        $publicationSlugs = ScopedRole::grantingSlugsFor($ability, ScopedRole::PIVOT_PUBLICATION);

        if (!$user || ($submissionSlugs === [] && $publicationSlugs === [])) {
            return $this->whereRaw('1 = 0');
        }

        return $this->where(function (Builder $query) use ($user, $submissionSlugs, $publicationSlugs) {
            if ($submissionSlugs !== []) {
                $query->whereHas('submissionAssignments', function (Builder $sub) use ($user, $submissionSlugs) {
                    $sub->where('user_id', $user->id)->whereIn('role', $submissionSlugs);
                });
            }
            if ($publicationSlugs !== []) {
                $query->orWhereHas('publication', function (Builder $pub) use ($user, $publicationSlugs) {
                    $pub->whereHas('users', function (Builder $u) use ($user, $publicationSlugs) {
                        $u->where('user_id', $user->id)->whereIn('role', $publicationSlugs);
                    });
                });
            }
        });
    }
}
