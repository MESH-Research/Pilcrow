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

        $wasBlocked = $user->hasModerationFlag(ModerationFlag::AvatarUploadBlocked);

        // "Blocking" sets the avatar-upload-blocked moderation flag;
        // "unblocking" clears it. Uploading is allowed by default. Audit only a
        // real state change so re-issuing the same state doesn't churn the log.
        if ($args['blocked']) {
            $user->setModerationFlag(ModerationFlag::AvatarUploadBlocked);
            if (!$wasBlocked) {
                $user->recordModerationAudit('avatar_upload_blocked', ['via' => 'admin']);
            }
        } else {
            $user->clearModerationFlag(ModerationFlag::AvatarUploadBlocked);
            if ($wasBlocked) {
                $user->recordModerationAudit('avatar_upload_unblocked', ['via' => 'admin']);
            }
        }

        return $user->refresh();
    }
}
