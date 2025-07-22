<?php

namespace App\Policies;

use App\Models\MetaPrompt;
use App\Models\Publication;
use App\Models\Role;
use App\Models\User;

class MetaPromptPolicy
{
    public function create(User $user, $args): bool
    {
        // Find the publication from the set.
        // Check if the user has the role of Application Administrator
        if ($user->hasRole(Role::APPLICATION_ADMINISTRATOR)) {
            return true;
        }

        $publication = Publication::whereHas('metaPages', fn($query) => $query->where('id', $args['meta_page_id']))->first();
        // Check if the user has a publication role that allows them to create a meta prompt
        if ($user->hasPublicationRole(Role::PUBLICATION_ADMINISTRATOR_ROLE_ID, $publication->id)) {
            return true;
        }

        return false;
    }

    public function update(User $user, MetaPrompt $metaPrompt): bool
    {
        if ($user->hasRole(Role::APPLICATION_ADMINISTRATOR)) {
            return true;
        }

        $publication = $metaPrompt->metaPage->publication;
        if ($user->hasPublicationRole(Role::PUBLICATION_ADMINISTRATOR_ROLE_ID, $publication->id)) {
            return true;
        }

        return false;
    }
}
