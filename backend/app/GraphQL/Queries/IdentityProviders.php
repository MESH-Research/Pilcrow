<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

final readonly class IdentityProviders
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args)
    {
        $providers = env('IDENTITY_PROVIDERS');
        return $providers ? json_decode($providers) : [];
    }
}
