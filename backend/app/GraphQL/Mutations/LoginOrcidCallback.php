<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use Laravel\Socialite\Facades\Socialite;

final readonly class LoginOrcidCallback
{
    /**
     * https://laravel.com/docs/master/socialite#routing
     *
     * @param null $_
     * @param array{} $args
     * @return void
     */
    public function __invoke(null $_, array $args)
    {
        $driver = Socialite::driver('orcid');
        $response = $driver->getAccessTokenResponse($args['code']);
        $user = $driver->getUserByToken($response['access_token']);
        print_r($user);
    }
}
