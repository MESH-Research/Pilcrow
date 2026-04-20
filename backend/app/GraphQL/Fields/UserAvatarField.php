<?php
declare(strict_types=1);

namespace App\GraphQL\Fields;

use App\Models\User;

class UserAvatarField
{
    /**
     * Resolve the User.avatar field.
     *
     * Returns null when the user has not uploaded an avatar so the client
     * can fall back to a placeholder.
     *
     * @param array<string, mixed> $_args
     * @return array<string, string>|null
     */
    public function __invoke(User $user, array $_args): ?array
    {
        $media = $user->getAvatarMedia();
        if ($media === null) {
            return null;
        }

        return [
            'url' => $media->getFullUrl(),
            'thumb_url' => $media->getFullUrl('thumb'),
            'medium_url' => $media->getFullUrl('medium'),
        ];
    }
}
