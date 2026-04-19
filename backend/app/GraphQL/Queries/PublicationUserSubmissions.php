<?php
declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\GraphQL\Dtos\PublicationUser as PublicationUserDto;
use App\Models\Submission;
use Illuminate\Contracts\Pagination\Paginator;

class PublicationUserSubmissions
{
    /**
     * Return submissions in this publication that the given user is
     * attached to, optionally filtered by the roles the user holds
     * on each submission and by phase (active / completed).
     *
     * Drafts are always excluded. Results are distinct per submission
     * even if the user holds multiple roles on the same one.
     *
     * @param \App\GraphQL\Dtos\PublicationUser $publicationUser
     * @param array $args
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function __invoke(
        PublicationUserDto $publicationUser,
        array $args
    ): Paginator {
        $completed = [
            Submission::REJECTED,
            Submission::ACCEPTED_AS_FINAL,
            Submission::ARCHIVED,
            Submission::DELETED,
        ];

        $query = Submission::query()
            ->select('submissions.*')
            ->distinct()
            ->join(
                'submission_user',
                'submission_user.submission_id',
                '=',
                'submissions.id'
            )
            ->where(
                'submissions.publication_id',
                $publicationUser->publication->id
            )
            ->where('submission_user.user_id', $publicationUser->user->id)
            ->where('submissions.status', '!=', Submission::DRAFT);

        if (! empty($args['roles'])) {
            $roleIds = array_map('intval', $args['roles']);
            $query->whereIn('submission_user.role_id', $roleIds);
        }

        $phase = $args['phase'] ?? null;
        if ($phase === 'completed') {
            $query->whereIn('submissions.status', $completed);
        } elseif ($phase === 'active') {
            $query->whereNotIn('submissions.status', $completed);
        }

        if (! empty($args['orderBy'])) {
            foreach ($args['orderBy'] as $order) {
                $query->orderBy(
                    $order['column'],
                    strtolower($order['order'])
                );
            }
        } else {
            $query->orderBy('submissions.updated_at', 'desc');
        }

        $page = $args['page'] ?? 1;
        $first = $args['first'];

        return $query->paginate($first, ['*'], 'page', $page);
    }
}
