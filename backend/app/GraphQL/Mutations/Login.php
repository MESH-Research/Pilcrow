<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Exceptions\InvalidCredentials;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Login
{
    /**
     * @param null  $_
     * @param array<string, mixed>  $args
     * @return \App\Models\User
     */
    public function __invoke($_, array $args, GraphQLContext $context): User
    {
        // Plain Laravel: Auth::guard()
        // Laravel Sanctum: Auth::guard(config('sanctum.guard', 'web'))
        $guard = Auth::guard('web');

        if (! $guard->attempt($args)) {
            throw new InvalidCredentials('Invalid credentials supplied');
        }

        /**
         * Since we successfully logged in, this can no longer be `null`.
         *
         * @var \App\Models\User $user
         */
        $user = $guard->user();

        // The Lighthouse context snapshots the request user when the GraphQL
        // request begins, so child field resolvers (notably @redactIfDenied on
        // User.email) would still see a null viewer without this update.
        $context->setUser($user);

        return $user;
    }
}
