<?php
declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Publication;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Nuwave\Lighthouse\Exceptions\AuthorizationException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class PublicationUserSearch
{
    /**
     * Search users related to the parent publication.
     *
     * Access:
     *   - Application administrators
     *   - Any user with a direct role on the publication (publication admin,
     *     editor) or assigned to a submission belonging to the publication
     *
     * Results: restricted to users with one of those same relations to the
     * publication. The search term, if provided, further narrows the results
     * via Scout. Empty/null term returns the full related-user set.
     */
    public function __invoke(Publication $publication, array $args, GraphQLContext $context): Collection
    {
        $viewer = $context->user();
        if ($viewer === null) {
            throw new AuthorizationException('Authentication required.');
        }

        $relatedIds = $this->relatedUserIds($publication);

        if (
            ! $viewer->hasRole(Role::APPLICATION_ADMINISTRATOR)
            && ! $relatedIds->contains($viewer->id)
        ) {
            throw new AuthorizationException(
                'You must be associated with this publication to search its users.'
            );
        }

        $term = $args['term'] ?? null;
        $query = User::query()->whereIn('id', $relatedIds);

        if (is_string($term) && $term !== '') {
            $matchedIds = User::search($term)->keys();
            $query->whereIn('id', $matchedIds);
        }

        return $query->orderBy('id')->limit(50)->get();
    }

    private function relatedUserIds(Publication $publication): Collection
    {
        $direct = $publication->users()->pluck('users.id');

        $viaSubmissions = User::query()
            ->whereHas('submissions', function ($q) use ($publication): void {
                $q->where('submissions.publication_id', $publication->id);
            })
            ->pluck('id');

        return $direct->merge($viaSubmissions)->unique()->values();
    }
}
