<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class TelemetryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
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
}
