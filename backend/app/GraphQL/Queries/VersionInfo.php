<?php
declare(strict_types=1);

namespace App\GraphQL\Queries;

final class VersionInfo
{
    /**
     * Return version information
     *
     * @param null $_
     * @param array $__
     * @return array
     */
    public function __invoke(null $_, array $__)
    {
        return [
          'version' => config('app.version'),
          'version_url' => config('app.version_url'),
          'version_date' => config('app.version_date'),
        ];
    }
}
