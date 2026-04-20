<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Permission;
use App\Models\User;

class SetUserAvatarUploadBlocked
{
    /**
     * Grant or revoke the avatar-upload block on a user.
     *
     * @param array{userId: string, blocked: bool} $args
     */
    public function __invoke(null $_, array $args): User
    {
        /** @var \App\Models\User $user */
        $user = User::findOrFail($args['userId']);

        if ($args['blocked']) {
            $user->givePermissionTo(Permission::AVATAR_UPLOAD_REVOKED);
        } else {
            $user->revokePermissionTo(Permission::AVATAR_UPLOAD_REVOKED);
        }

        return $user->refresh();
    }
}
