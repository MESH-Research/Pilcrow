<?php
declare(strict_types=1);

namespace App\GraphQL\Fields;

use App\Models\Permission;
use App\Models\User;

class UserAvatarBlockedField
{
    /**
     * True when the user does not have the UPLOAD_AVATAR permission —
     * either because a moderator revoked it, or (shouldn't happen, but
     * defensive) because they were never granted it in the first place.
     *
     * @param array<string, mixed> $_args
     */
    public function __invoke(User $user, array $_args): bool
    {
        return !$user->hasPermissionTo(Permission::UPLOAD_AVATAR);
    }
}
