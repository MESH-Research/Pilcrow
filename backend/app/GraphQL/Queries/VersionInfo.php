<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

final readonly class VersionInfo
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args)
    {
        return [
          'version' => config('app.version'),
          'version_url' => config('app.version_url'),
          'version_date' => config('app.version_date'),
        ];
    }
}
