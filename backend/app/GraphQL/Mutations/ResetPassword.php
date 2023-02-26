<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Exceptions\ClientException;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class ResetPassword
{
    /**
     * Send a password reset email to a specified email address
     *
     * @param [type] $_
     * @param array $args
     * @return bool
     */
    public function request($_, array $args)
    {
        try {
            /** @var \App\Models\User **/
            $user = User::where('email',$args['email'])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new ClientException('Not Found', 'requestPasswordReset', 'EMAIL_NOT_FOUND');
        }
        $user->sendPasswordResetLink(['email' => $args['email']]);
        return true;
    }

    /**
     * Update a password from a password reset request"
     *
     * @param [type] $_
     * @param array $args
     * @return bool
     */
    public function reset($_, array $args)
    {
        try {
            /** @var \App\Models\User **/
            $user = User::where('email',$args['email'])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new ClientException('Not Found', 'requestPasswordReset', 'EMAIL_NOT_FOUND');
        }
        // TODO: Update password
        return true;
    }
}
