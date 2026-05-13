<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // The sentry/sentry-laravel package auto-registers a reportable
        // handler via package discovery. It reads config/sentry.php and
        // becomes a no-op when the DSN is unset (i.e. TELEMETRY_ENABLED=false
        // or TELEMETRY_DSN empty). No explicit wiring needed here.
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
