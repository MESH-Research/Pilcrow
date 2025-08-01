<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\MetaPrompt;
use App\Models\Publication;
use App\Models\Role;
use App\Models\User;

class MetaPromptPolicy
{
    /**
     * @param \App\Models\User $user
     * @param [type] $args
     * @return bool
     */
    public function create(User $user, $args): bool
    {
        // Check if the user has the role of Application Administrator
        if ($user->hasRole(Role::APPLICATION_ADMINISTRATOR)) {
            return true;
        }

        // Check if the user has a publication role that allows them to create a meta prompt
        $publication = Publication::whereMetaForm($args['meta_form_id'])
            ->whereAdmin($user->id)
            ->exists();

        if ($publication) {
            return true;
        }

        return false;
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\MetaPrompt $metaPrompt
     * @return bool
     */
    public function update(User $user, MetaPrompt $metaPrompt): bool
    {
        if ($user->hasRole(Role::APPLICATION_ADMINISTRATOR)) {
            return true;
        }

        $publication = Publication::whereMetaForm($metaPrompt->meta_form_id)
            ->whereAdmin($user->id)
            ->exists();

        if ($publication) {
            return true;
        }

        return false;
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\MetaPrompt $metaPrompt
     * @return bool
     */
    public function delete(User $user, MetaPrompt $metaPrompt): bool
    {
        if ($user->hasRole(Role::APPLICATION_ADMINISTRATOR)) {
            return true;
        }

        $publication = Publication::whereMetaForm($metaPrompt->meta_form_id)
            ->whereAdmin($user->id)
            ->exists();

        if ($publication) {
            return true;
        }

        return false;
    }
}
