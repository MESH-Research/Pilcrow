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

        // "Blocking" means revoking the default UPLOAD_AVATAR permission;
        // "unblocking" grants it back directly on the user.
        if ($args['blocked']) {
            $user->revokePermissionTo(Permission::UPLOAD_AVATAR);
        } else {
            $user->givePermissionTo(Permission::UPLOAD_AVATAR);
        }

        return $user->refresh();
    }
}
