<?php
declare(strict_types=1);

namespace App\GraphQL\Queries;

final readonly class IdentityProviders
{
    /**
     * @param null $_
     * @param array $_args
     * @return string
     */
    public function __invoke(null $_, array $_args): string
    {
        $providers = env('IDENTITY_PROVIDERS');

        return $providers ? json_decode($providers) : [];
    }
}
