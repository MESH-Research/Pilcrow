<?php
declare(strict_types=1);

namespace App\GraphQL\Queries;

use Illuminate\Support\Collection;

final readonly class IdentityProviders
{
    /**
     * @param null $_
     * @param array $_args
     * @return array
     */
    public function __invoke(null $_, array $_args): Collection
    {
        $providers = config('app.external_oauth_providers');

        $availableProviders = new Collection($providers);

        return $availableProviders->filter(function ($adapter) {
            return $adapter::isEnabled();
        })->map(function ($adapter, $name) {
            return [
                'name' => $name,
                'login_url' => $adapter::getLoginUrl(),
                'label' => $adapter::getLabel(),
                'icon' => $adapter::getIcon(),
            ];
        });
    }
}
