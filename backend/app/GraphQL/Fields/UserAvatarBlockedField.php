<?php
declare(strict_types=1);

namespace App\GraphQL\Fields;

use App\Models\Permission;
use App\Models\User;

class UserAvatarBlockedField
{
    /**
     * True when the user has been granted the AVATAR_UPLOAD_REVOKED
     * permission (i.e. a moderator has blocked them from uploading).
     *
     * @param array<string, mixed> $_args
     */
    public function __invoke(User $user, array $_args): bool
    {
        return $user->hasPermissionTo(Permission::AVATAR_UPLOAD_REVOKED);
    }
}
