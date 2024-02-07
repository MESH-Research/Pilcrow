<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\ExternalIdentityProvider;
use App\Models\User;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

final readonly class RegisterOauthUser
{
    /**
     * @param null $_
     * @param array{} $args
     * @return array
     */
    public function register(null $_, array $args)
    {
        try {

            $user = User::create([
                'name' => $args['user']['name'],
                'email' => $args['user']['email'],
                'password' => ''
            ]);

            ExternalIdentityProvider::create([
                'provider_name' => $args['provider']['name'],
                'provider_id' => $args['provider']['id'],
                'user_id' => $user->id,
            ]);

            Auth::login($user);

            return $user;

        } catch (\Exception $e) {
            throw new Error($e->getMessage());
        }
    }

}
