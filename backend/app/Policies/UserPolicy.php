<?php
declare(strict_types=1);

namespace App\Policies;

use App\Auth\Ability;
use App\Auth\AbilityResolver;
use App\Auth\ScopedRole;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * @param \App\Auth\AbilityResolver $abilities
     */
    public function __construct(private AbilityResolver $abilities)
    {
    }

    /**
     * Determine whether the viewer can list user accounts via the top-level
     * users query. Restricted to application administrators.
     */
    public function viewAny(User $viewer): bool
    {
        return $this->abilities->allows($viewer, Ability::UserViewAny);
    }

    /**
     * Determine whether the viewer can fetch another user's record via the
     * top-level user(id) query. Restricted to application administrators.
     */
    public function view(User $viewer, User $_target): bool
    {
        return $this->abilities->allows($viewer, Ability::UserView);
    }

    /**
     * Determine whether the viewer can grant or revoke a user's beta
     * access. Restricted to application administrators.
     *
     * @param \App\Models\User  $viewer
     * @param \App\Models\User  $_target
     * @return bool
     */
    public function manageBeta(User $viewer, User $_target): bool
    {
        return $this->abilities->allows($viewer, Ability::UserManageBeta);
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

        // TODO: Check if user can update user within own publication
        return $this->abilities->allows($user, Ability::UserUpdate);
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

        if ($viewer->isApplicationAdministrator()) {
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
                        ScopedRole::PublicationAdmin->pivotValue(),
                        ScopedRole::Editor->pivotValue(),
                    ])
                    ->pluck('publications.id')
            );
        }
        $viewerPublicationIds = $viewer->getRelation('privilegedPublicationIds');

        if ($viewerPublicationIds->isEmpty()) {
            return false;
        }

        if (
            $target->publications()
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
