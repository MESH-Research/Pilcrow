<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use Laravel\Socialite\Facades\Socialite;

final readonly class LoginOrcid
{
    /**
     * @link https://github.com/SocialiteProviders/Orcid
     * @param null $_
     * @param array{} $_args
     * @return string
     */
    public function __invoke(null $_, array $_args): string
    {
        /** @var \App\GraphQL\Mutations\AbstractProvider $driver */
        $driver = Socialite::driver('orcid');

        return $driver->setScopes(['/authenticate'])->redirect()->getTargetUrl();
    }
}
