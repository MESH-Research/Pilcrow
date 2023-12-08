<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use Laravel\Socialite\Facades\Socialite;

final readonly class LoginOrcid
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args)
    {
        return Socialite::driver('orcid')->redirect();
    }
}
