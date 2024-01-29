<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use Laravel\Socialite\Facades\Socialite;

final readonly class LoginOrcidCallback
{
    /**
     * @link https://laravel.com/docs/master/socialite#routing
     * @param null $_
     * @param array{} $args
     * @return void
     */
    public function __invoke(null $_, array $args)
    {
        try {
            /** @var \App\GraphQL\Mutations\AbstractProvider $driver */
            $driver = Socialite::driver('orcid');
            $response = $driver->getAccessTokenResponse($args['code']);
            $user = $driver->userFromToken($response);

            print_r($user);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
