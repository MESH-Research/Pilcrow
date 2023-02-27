<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Exceptions\ClientException;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final class ResetPassword
{
    /**
     * Send a password reset request email to a specified email address
     *
     * @param [type] $_
     * @param array $args
     * @return bool
     */
    public function request($_, array $args)
    {
        try {
            User::where('email', $args['email'])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new ClientException('Not Found', 'requestPasswordReset', 'EMAIL_NOT_FOUND');
        }
        Password::sendResetLink(
            ['email' => $args['email']]
        );

        return true;
    }

    /**
     * Update a password from a password reset request
     *
     * @param [type] $_
     * @param array $args
     * @return mixed
     */
    public function reset($_, array $args)
    {
        try {
            /** @var \App\Models\User $user **/
            $user = User::where('email', $args['email'])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new ClientException('Not Found', 'resetPassword', 'EMAIL_NOT_FOUND');
        }
        $status = Password::reset(
            [
                'email' => $args['email'],
                'password' => $args['password'],
                'token' => $args['token'],
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );
        if ($status !== Password::PASSWORD_RESET) {
            throw new ClientException('Invalid', 'resetPassword', 'ERROR');
        }
        $guard = Auth::guard('web');
        $guard->login($user);

        return $user;
    }
}
