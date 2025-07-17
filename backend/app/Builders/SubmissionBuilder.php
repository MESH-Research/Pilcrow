<?php

namespace App\Builders;

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
                ->whereIn('role_id', $roles);
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
            $query->whereIn('role_id', $roles);
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
        $user = Auth::user();

        return $this->where(function ($query) use ($user) {
            $query->whereHas('publication', function ($query) use ($user) {
                $query->whereHas('users', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            })->orWhereHas('submissionAssignments', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        });
    }
}
