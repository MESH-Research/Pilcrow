<?php

namespace App\Providers;

use App\Models\InlineComment;
use App\Models\OverallComment;
use App\Models\User;
use App\Policies\CommentPolicy;
use App\Policies\UserPolicy;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Explicit policy map. One CommentPolicy serves both comment models — they
     * share an identical, comment-scoped rule (see the policy) — so it is mapped
     * rather than auto-discovered (which would need a policy class per model).
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        InlineComment::class => CommentPolicy::class,
        OverallComment::class => CommentPolicy::class,
    ];

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

        //
    }
}
