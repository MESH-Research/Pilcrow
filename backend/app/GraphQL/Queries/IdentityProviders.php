<?php
declare(strict_types=1);

namespace App\GraphQL\Queries;

final readonly class IdentityProviders
{
    /**
     * @param null $_
     * @param array $_args
     * @return array
     */
    public function __invoke(null $_, array $_args): array
    {
        $providers = env('IDENTITY_PROVIDERS');

        return $providers ? json_decode($providers) : [];
    }
}
