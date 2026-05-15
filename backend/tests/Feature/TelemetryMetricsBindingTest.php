<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Telemetry\Metrics;
use Tests\TestCase;

class TelemetryMetricsBindingTest extends TestCase
{
    public function testMetricsDisabledByDefault(): void
    {
        config([
            'app.telemetry.enabled' => false,
            'app.telemetry.dsn' => null,
        ]);

        $metrics = $this->app->make(Metrics::class);
        $this->assertFalse($metrics->isEnabled());
    }

    public function testMetricsDisabledWhenDsnMissing(): void
    {
        config([
            'app.telemetry.enabled' => true,
            'app.telemetry.dsn' => null,
        ]);

        $this->app->forgetInstance(Metrics::class);
        $metrics = $this->app->make(Metrics::class);
        $this->assertFalse($metrics->isEnabled());
    }

    public function testMetricsEnabledWithDsnAndFlag(): void
    {
        config([
            'app.telemetry.enabled' => true,
            'app.telemetry.dsn' => 'https://public@sentry.example.com/1',
        ]);

        $this->app->forgetInstance(Metrics::class);
        $metrics = $this->app->make(Metrics::class);
        $this->assertTrue($metrics->isEnabled());
    }

    public function testCountAndFlushAreNoOpsWhenDisabled(): void
    {
        config([
            'app.telemetry.enabled' => false,
            'app.telemetry.dsn' => null,
        ]);

        $this->app->forgetInstance(Metrics::class);
        $metrics = $this->app->make(Metrics::class);

        // Should not throw even though Sentry SDK is not initialized.
        $metrics->count('test.event');
        $metrics->gauge('test.gauge', 1);
        $metrics->distribution('test.distribution', 1.5);
        $metrics->flush();

        $this->assertTrue(true);
    }
}
