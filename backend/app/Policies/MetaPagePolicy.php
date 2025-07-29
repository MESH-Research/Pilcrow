<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\MetaPage;
use App\Models\Role;
use App\Models\User;

class MetaPagePolicy
{
    public function update(User $user, MetaPage $metaPage): bool
    {
        // Check if the user has the role of Application Administrator
        if ($user->hasRole(Role::APPLICATION_ADMINISTRATOR)) {
            return true;
        }

        // Check if the user has a publication role that allows them to update the meta page
        if ($user->hasPublicationRole(Role::PUBLICATION_ADMINISTRATOR_ROLE_ID, $metaPage->publication_id)) {
            return true;
        }

        return false;
    }

    public function create(User $user, array $args): bool
    {
        // Check if the user has the role of Application Administrator
        if ($user->hasRole(Role::APPLICATION_ADMINISTRATOR)) {
            return true;
        }

        // Check if the user has a publication role that allows them to create a meta page
        if ($user->hasPublicationRole(Role::PUBLICATION_ADMINISTRATOR_ROLE_ID, $args['publication_id'] ?? null)) {
            return true;
        }

        return false;
    }

    public function delete(User $user, MetaPage $metaPage)
    {
        // Check if the user has the role of Application Administrator
        if ($user->hasRole(Role::APPLICATION_ADMINISTRATOR)) {
            return true;
        }

        // Check if the user has a publication role that allows them to delete the meta page
        if ($user->hasPublicationRole(Role::PUBLICATION_ADMINISTRATOR_ROLE_ID, $metaPage->publication_id)) {
            return true;
        }

        return false;
    }
}
