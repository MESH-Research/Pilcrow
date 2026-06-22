<?php
declare(strict_types=1);

namespace App\Policies;

use App\Auth\GlobalAbility;
use App\Auth\PublicationAbility;
use App\Auth\ScopedAbilityResolver;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublicationPolicy
{
    use HandlesAuthorization;

    /**
     * @param \App\Auth\ScopedAbilityResolver $scoped
     */
    public function __construct(private ScopedAbilityResolver $scoped)
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
        return $user->can(GlobalAbility::PublicationCreate);
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

        return $this->scoped->allows($user, PublicationAbility::Update, $publication);
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

        return $this->scoped->allows($user, PublicationAbility::View, $publication);
    }
}
