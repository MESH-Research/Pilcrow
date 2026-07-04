<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations\IntegrationTesting;

use App\IntegrationTesting\TableSnapshot;
use Illuminate\Support\Facades\App;

class TeardownTestToken
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
            'Test token teardown is not available in this environment.'
        );

        $token = $args['token'];

        TableSnapshot::drop($token);

        return $token;
    }
}
