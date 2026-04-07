<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\IntegrationTesting\TableSnapshot;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IntegrationTestingMiddleware
{
    /**
     * If the request has an X-Test-Token header, switch the DB connection
     * to use this worker's prefixed shadow tables.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! App::environment(['local', 'testing'])) {
            return $next($request);
        }

        // Always reset prefix at the start of every request to prevent
        // stale prefixes from a previous request on this FPM connection.
        DB::connection()->setTablePrefix('');

        $token = $request->header('X-Test-Token');

        if (! $token) {
            return $next($request);
        }

        app()->instance('test.token', $token);

        $prefix = TableSnapshot::prefix($token);

        if (TableSnapshot::exists($token)) {
            DB::connection()->setTablePrefix($prefix);
        } else {
            Log::channel('single')->debug(
                "[IntegrationTestingMiddleware] Token {$token} — no shadow tables."
            );
        }

        try {
            return $next($request);
        } finally {
            DB::connection()->setTablePrefix('');
        }
    }
}
