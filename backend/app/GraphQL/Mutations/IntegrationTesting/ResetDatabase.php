<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations\IntegrationTesting;

use App\IntegrationTesting\TableSnapshot;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ResetDatabase
{
    /**
     * @param mixed  $_  unused root value
     * @param array<string, mixed>  $_args  unused arguments
     * @return string
     */
    public function __invoke($_, array $_args): string
    {
        abort_unless(
            App::environment(['local', 'testing']),
            403,
            'Reset database is not available in this environment.'
        );

        $start = microtime(true);

        TableSnapshot::cleanup();
        Artisan::call('migrate:fresh', ['--seed' => true]);

        $ms = round((microtime(true) - $start) * 1000);
        Log::channel('single')->info("[ResetDatabase] migrate:fresh --seed completed ({$ms}ms)");

        return Artisan::output();
    }
}
