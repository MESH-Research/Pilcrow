<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use Laravel\Socialite\Facades\Socialite;

final readonly class LoginOrcid
{
    /**
     * https://github.com/SocialiteProviders/Orcid
     *
     * @param null $_
     * @param  array{}  $args
     * @return string
     */
    public function __invoke(null $_, array $args): string
    {
        return Socialite::driver('orcid')->redirect()->getTargetUrl();
    }
}
