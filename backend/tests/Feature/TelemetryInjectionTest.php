<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class TelemetryInjectionTest extends TestCase
{
    public function testTelemetryDisabledByDefault(): void
    {
        config([
            'app.telemetry.enabled' => false,
            'app.telemetry.dsn' => null,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('"enabled":false', false);
        $response->assertSee('"dsn":null', false);
    }

    public function testTelemetryInjectedWhenEnabledWithDsn(): void
    {
        config([
            'app.telemetry.enabled' => true,
            'app.telemetry.dsn' => 'https://public@example.ingest.sentry.io/1',
            'app.telemetry.environment' => 'staging',
            'app.telemetry.traces_sample_rate' => 0.25,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('"enabled":true', false);
        $response->assertSee('"dsn":"https:\/\/public@example.ingest.sentry.io\/1"', false);
        $response->assertSee('"environment":"staging"', false);
        $response->assertDontSee('"release"', false);
        $response->assertSee('"tracesSampleRate":0.25', false);
        $response->assertDontSee('replays', false);
    }

    public function testTelemetryDsnSuppressedWhenDisabledEvenIfSet(): void
    {
        config([
            'app.telemetry.enabled' => false,
            'app.telemetry.dsn' => 'https://public@example.ingest.sentry.io/1',
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('"enabled":false', false);
        $response->assertSee('"dsn":null', false);
        $response->assertDontSee('public@example.ingest.sentry.io', false);
    }
}
