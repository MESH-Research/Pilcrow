<?php

namespace App\Providers;

use App\Auth\Abilities\GlobalAbility;
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

        // Avatar moderation is a single application-wide capability, not tied
        // to any one model, so it is a Gate rather than a per-model policy
        // method. The GraphQL avatar-moderation mutations/queries reference it
        // via @can(ability: "moderateAvatars"). Application administrators hold
        // it through Bouncer's everything() grant.
        Gate::define('moderateAvatars', function (User $user) {
            return $user->can(GlobalAbility::AvatarModerate);
        });
    }
}
