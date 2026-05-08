<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the viewer can fetch another user's record via the
     * top-level user(id) query. Restricted to application administrators.
     */
    public function view(User $viewer, User $target): bool
    {
        return $viewer->hasRole(Role::APPLICATION_ADMINISTRATOR);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User  $user
     * @param \App\Models\User  $model
     * @return bool
     */
    public function update(User $user, User $model)
    {
        //User is updating their own record.
        if ($user->id === $model->id) {
            return true;
        }
        //User has global permission to update users
        if ($user->can(Permission::UPDATE_USERS)) {
            return true;
        }

        // TODO: Check if user can update user within own publication
        // No explicit permission so return false.
        return false;
    }

    /**
     * Determine whether the viewer can see the target user's email address.
     *
     * Visibility rules:
     *  - The user themselves
     *  - Application administrators
     *  - Publication administrators / editors of any publication the target
     *    belongs to, or that owns a submission the target is assigned to
     */
    public function viewEmail(User $viewer, User $target): bool
    {
        if ($viewer->id === $target->id) {
            return true;
        }

        if ($viewer->hasRole(Role::APPLICATION_ADMINISTRATOR)) {
            return true;
        }

        // Memoize the viewer's privileged publication IDs on the model
        // instance so list resolvers (submission.reviewers, userSearch, ...)
        // don't re-run the same query for every target user in the response.
        if (!$viewer->relationLoaded('privilegedPublicationIds')) {
            $viewer->setRelation(
                'privilegedPublicationIds',
                $viewer->publications()
                    ->wherePivotIn('role_id', [
                        Role::PUBLICATION_ADMINISTRATOR_ROLE_ID,
                        Role::EDITOR_ROLE_ID,
                    ])
                    ->pluck('publications.id')
            );
        }
        $viewerPublicationIds = $viewer->getRelation('privilegedPublicationIds');

        if ($viewerPublicationIds->isEmpty()) {
            return false;
        }

        if ($target->publications()
            ->whereIn('publications.id', $viewerPublicationIds)
            ->exists()
        ) {
            return true;
        }

        return $target->submissions()
            ->whereIn('submissions.publication_id', $viewerPublicationIds)
            ->exists();
    }
}
