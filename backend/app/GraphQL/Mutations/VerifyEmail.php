<?php

namespace App\GraphQL\Mutations;

use App\Exceptions\ClientException;
use App\Models\User;
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
            throw new ClientException('Token Expired', 'emailVerification', 'VERIFY_TOKEN_EXPIRED');
        }

        if (!$user->verifyEmailToken($args['token'], $args['expires'])) {
            throw new ClientException('Invalid token', 'emailVerification', 'VERIFY_TOKEN_INVALID');
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
        $currentUser = Auth::user();
        $userId = $args['id'] ?? Auth::user()->id;
        
        $user = User::find($userId);
        
        if (!$user) {
            throw new ClientException('Not Found', 'emailVerification', 'VERIFY_USER_NOT_FOUND');
        }

        if ($currentUser->cannot('update', $user)) {
            throw new ClientException('Not authorized.', 'authentication', 'NOT_AUTHORIZED');
        }
        
        

        if ($user->hasVerifiedEmail()) {
            throw new ClientException('Already verified', 'emailVerification', 'VERIFY_EMAIL_VERIFIED');
        }

        $user->sendEmailVerificationNotification();
        return $user;
    }
}
