<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use Laravel\Socialite\Facades\Socialite;

final readonly class LoginGoogle
{
    /**
     * @link https://github.com/SocialiteProviders/Providers/tree/master/src/Google
     * @param null $_
     * @param array{} $_args
     * @return string
     */
    public function __invoke(null $_, array $_args): string
    {
        /** @var \App\GraphQL\Mutations\AbstractProvider $driver */
        $driver = Socialite::driver('google');

        return $driver->redirect()->getTargetUrl();
        // Socialite::driver('google')->redirect();
    }
}
