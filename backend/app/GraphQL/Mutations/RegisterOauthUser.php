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
                'name' => $args['input']['user']['name'],
                'username' => $args['input']['user']['username'],
                'email' => $args['input']['user']['email'],
            ]);

            ExternalIdentityProvider::create([
                'provider_name' => $args['input']['provider']['provider_name'],
                'provider_id' => $args['input']['provider']['provider_id'],
                'user_id' => $user->id,
            ]);

            Auth::guard('web')->login($user);

            return $user;

        } catch (\Exception $e) {
            report($e);
            throw new Error($e->getMessage());
        }
    }
}

