<?php

namespace App\Providers;

use App\Auth\Abilities\GlobalAbility;
use App\Enums\ModerationFlag;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        VerifyEmail::$createUrlCallback = function ($notifiable) {
            return $notifiable->getEmailVerificationUrl();
        };

        // Whether a user may upload (or replace) an avatar. A server-owned,
        // attribute-based decision — owner AND not blocked by a moderator —
        // rather than an imperative throw in the resolver re-derived again on
        // the client. Upload is self-service only: moderators clear avatars,
        // they never replace them, so there is no moderator disjunct here.
        Gate::define('uploadAvatar', function (User $user, User $target): bool {
            return $user->id === $target->id
                && !$target->hasModerationFlag(ModerationFlag::AvatarUploadBlocked);
        });

        // Whether a user may remove an avatar: the owner (self-service) OR an
        // avatar moderator (takedown). A disjunction of single-subject
        // abilities at the call site, not an `|| can(moderate)` folded into a
        // capability boolean.
        Gate::define('deleteAvatar', function (User $user, User $target): bool {
            return $user->id === $target->id
                || $user->can(GlobalAbility::AdminAvatarModerate);
        });
    }
}
