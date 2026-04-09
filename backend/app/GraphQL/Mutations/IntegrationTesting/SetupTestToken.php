<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations\IntegrationTesting;

use App\IntegrationTesting\TableSnapshot;
use Illuminate\Support\Facades\App;

class SetupTestToken
{
    /**
     * @param mixed  $_  unused root value
     * @param array<string, mixed>  $args
     * @return string
     */
    public function __invoke($_, array $args): string
    {
        abort_unless(
            App::environment(['local', 'testing']),
            403,
            'Test token setup is not available in this environment.'
        );

        $token = $args['token'];

        // Clean up any stale shadow tables for this token
        if (TableSnapshot::exists($token)) {
            TableSnapshot::drop($token);
        }

        TableSnapshot::capture($token);

        return $token;
    }
}
