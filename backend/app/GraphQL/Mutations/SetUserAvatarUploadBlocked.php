<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Enums\ModerationFlag;
use App\Models\User;

class SetUserAvatarUploadBlocked
{
    /**
     * Set or clear the avatar-upload block on a user.
     *
     * @param array{userId: string, blocked: bool} $args
     */
    public function __invoke(null $_, array $args): User
    {
        /** @var \App\Models\User $user */
        $user = User::findOrFail($args['userId']);

        // "Blocking" sets the avatar-upload-blocked moderation flag;
        // "unblocking" clears it. Uploading is allowed by default.
        if ($args['blocked']) {
            $user->setModerationFlag(ModerationFlag::AvatarUploadBlocked);
        } else {
            $user->clearModerationFlag(ModerationFlag::AvatarUploadBlocked);
        }

        return $user->refresh();
    }
}
