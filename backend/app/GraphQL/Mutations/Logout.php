<?php
declare(strict_types=1);

namespace App\Graphql\mutations;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Logout
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $_args
     * @return \App\Models\User
     */
    public function __invoke($_, array $_args): ?User
    {
        // Plain Laravel: Auth::guard()
        // Laravel Sanctum: Auth::guard(config('sanctum.guard', 'web'))
        $guard = Auth::guard('web');

        /** @var \App\Models\User|null $user */
        $user = $guard->user();
        $guard->logout();

        return $user;
    }
}
