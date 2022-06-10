<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Publication;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublicationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        if ($user->hasRole(Role::APPLICATION_ADMINISTRATOR)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update a publication.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Publication $publication
     * @return bool
     */
    public function update(User $user, Publication $publication)
    {
        if ($user->hasRole(Role::APPLICATION_ADMINISTRATOR)) {
            return true;
        }

        if ($user->hasPublicationRole(Role::PUBLICATION_ADMINISTRATOR_ROLE_ID, $publication->id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view publications.
     *
     * @param  \App\Models\User  $user
     * @param \App\Models\Publication $publication
     * @return bool
     */
    public function view(User $user, Publication $publication)
    {
        if ($user->hasRole(Role::APPLICATION_ADMINISTRATOR)) {
            return true;
        }

        if ($publication->is_publicly_visible) {
            return true;
        }

        if ($user->hasPublicationRole('*', $publication->id)) {
            return true;
        }

        return false;
    }
}
