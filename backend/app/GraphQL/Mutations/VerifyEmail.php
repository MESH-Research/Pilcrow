<?php

namespace App\GraphQL\Mutations;

use App\Exceptions\ClientException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class VerifyEmail
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args)
    {
        $user = Auth::user();
        $now = Carbon::now()->timestamp;
        if ($now > $args['expires']) {
            throw new ClientException('Token Expired', 'emailVerification', 'TOKEN_EXPIRED');
        }

        if (!$user->verifyEmailToken($args['token'], $args['expires'])) {
            throw new ClientException('Invalid token', 'emailVerification', 'TOKEN_INVALID');
        }

        $user->markEmailAsVerified();

        return $user;

        

    }
}
