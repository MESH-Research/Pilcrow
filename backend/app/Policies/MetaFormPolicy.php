<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\MetaForm;
use App\Models\Publication;
use App\Models\Role;
use App\Models\User;

class MetaFormPolicy
{
    /**
     * @param \App\Models\User $user
     * @param \App\Models\MetaForm $metaForm
     * @return bool
     */
    public function update(User $user, MetaForm $metaForm): bool
    {
        // Check if the user has the role of Application Administrator
        if ($user->hasRole(Role::APPLICATION_ADMINISTRATOR)) {
            return true;
        }

        // Check if the user has a publication role that allows them to update the meta form
        $publication = Publication::whereMetaForm($metaForm->id)
            ->whereAdmin($user->id)
            ->exists();

        if ($publication) {
            return true;
        }

        return false;
    }

    /**
     * @param \App\Models\User $user
     * @param [type] $args
     * @return bool
     */
    public function create(User $user, array $args): bool
    {
        // Check if the user has the role of Application Administrator
        if ($user->hasRole(Role::APPLICATION_ADMINISTRATOR)) {
            return true;
        }

        // Check if the user has a publication role that allows them to create a meta form
        $publication = Publication::where('id', $args['publication_id'])
            ->whereAdmin($user->id)
            ->exists();

        if ($publication) {
            return true;
        }

        return false;
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\MetaForm $metaForm
     * @return bool
     */
    public function delete(User $user, MetaForm $metaForm): bool
    {
        // Check if the user has the role of Application Administrator
        if ($user->hasRole(Role::APPLICATION_ADMINISTRATOR)) {
            return true;
        }

        // Check if the user has a publication role that allows them to delete the meta form
        $publication = Publication::whereMetaForm($metaForm->id)
            ->whereAdmin($user->id)
            ->exists();

        if ($publication) {
            return true;
        }

        return false;
    }
}
