<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Publication;
use App\Models\Submission;
use App\Telemetry\Metrics;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class TelemetryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Metrics::class, function (Application $app): Metrics {
            $enabled = (bool) config('app.telemetry.enabled')
                && filled(config('app.telemetry.dsn'));

            return new Metrics($enabled);
        });

        if (filled(config('app.telemetry.release'))) {
            return;
        }

        $version = config('pilcrow.version');
        if (! filled($version)) {
            return;
        }

        $release = 'pilcrow-backend@' . $version;
        config([
            'app.telemetry.release' => $release,
            'sentry.release' => $release,
        ]);
    }

    public function boot(): void
    {
        $this->registerMetricEmitters();

        if (! config('app.telemetry.enabled') || ! filled(config('app.telemetry.dsn'))) {
            return;
        }

        if (! class_exists(\Sentry\State\Scope::class)) {
            return;
        }

        // Tag every event with the application role of the authenticated user
        // and the resolved publication slug, without sending email or name.
        \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
            $user = Auth::user();
            if ($user instanceof Authenticatable) {
                $scope->setUser([
                    'id' => $user->getAuthIdentifier(),
                ]);

                if (method_exists($user, 'getRoleNames')) {
                    $scope->setTag('user.roles', $user->getRoleNames()->implode(','));
                }
            }
        });
    }

    /**
     * Aggregate counters only. No user ids, emails, or other PII in attributes.
     * Wired even when telemetry is disabled — Metrics short-circuits to no-op.
     */
    private function registerMetricEmitters(): void
    {
        $metrics = fn (): Metrics => $this->app->make(Metrics::class);

        Event::listen(Login::class, function () use ($metrics): void {
            $metrics()->count('auth.login_success');
        });

        Event::listen(Failed::class, function () use ($metrics): void {
            $metrics()->count('auth.login_failed');
        });

        Event::listen(Registered::class, function () use ($metrics): void {
            $metrics()->count('auth.signup');
        });

        Event::listen(Verified::class, function () use ($metrics): void {
            $metrics()->count('auth.email_verified');
        });

        Publication::created(function () use ($metrics): void {
            $metrics()->count('publication.created');
        });

        Submission::created(function () use ($metrics): void {
            $metrics()->count('submission.created');
        });
    }
}
