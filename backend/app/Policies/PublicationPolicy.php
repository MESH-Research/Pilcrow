<?php
declare(strict_types=1);

namespace App\Policies;

use App\Auth\AbilityResolver;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublicationPolicy
{
    use HandlesAuthorization;

    public function __construct(private AbilityResolver $abilities)
    {
    }

    /**
     * Determine whether the user can create models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $this->abilities->allows($user, 'publication.create');
    }

    /**
     * Determine whether the user can update a publication.
     *
     * @param \App\Models\User $user
     * @param array $args
     * @return bool
     */
    public function update(User $user, array $args)
    {
        $publication = Publication::find($args['id']);

        return $this->abilities->allows($user, 'publication.update', $publication);
    }

    /**
     * Determine whether the user can view publications.
     *
     * Publicly visible publications are viewable by anyone (including guests);
     * otherwise the publication.view ability is required.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Publication $publication
     * @return bool
     */
    public function view(?User $user, Publication $publication)
    {
        if ($publication->is_publicly_visible) {
            return true;
        }
        if ($user === null) {
            return false;
        }

        return $this->abilities->allows($user, 'publication.view', $publication);
    }
}
