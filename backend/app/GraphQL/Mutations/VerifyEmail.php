<?php

namespace App\GraphQL\Mutations;

use App\Exceptions\ClientException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class VerifyEmail
{
    /**
     * Verify the current user's email.
     * 
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function verify($_, array $args)
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

    /**
     * Resend verification email to a user.
     * 
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function send($_, array $args)
    {
        $user = Auth::user();
        
        if (!empty($args['id'])) {
            //TODO: Add permissions check to allow admin users to send email based on UPDATE_USERS permission.
            if ($args['id'] != $user->id ) {
                throw new ClientException('Sending verification for another user is not implemented.', 'emailVerification', 'NOT_IMPLEMENTED');
            }

        }
        
        if (!$user) {
            throw new ClientException('Not Found', 'emailVerification', 'NOT_FOUND');
        }

        if ($user->hasVerifiedEmail()) {
            throw new ClientException('Already verified', 'emailVerification', 'EMAIL_VERIFIED');
        }

        $user->sendEmailVerificationNotification();
        return $user;
    }
}
