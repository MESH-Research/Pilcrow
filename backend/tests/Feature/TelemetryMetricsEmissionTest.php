<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use App\Telemetry\Metrics;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Sentry\Unit;
use Tests\TestCase;

class TelemetryMetricsEmissionTest extends TestCase
{
    use RefreshDatabase;

    private FakeMetrics $metrics;

    protected function setUp(): void
    {
        parent::setUp();

        $this->metrics = new FakeMetrics();
        $this->app->instance(Metrics::class, $this->metrics);
    }

    public function testLoginEventEmitsSuccessCounter(): void
    {
        $user = User::factory()->create();
        Event::dispatch(new Login('web', $user, false));

        $this->assertSame(1, $this->metrics->totalFor('auth.login_success'));
    }

    public function testFailedEventEmitsFailureCounter(): void
    {
        Event::dispatch(new Failed('web', null, ['email' => 'x@example.test']));

        $this->assertSame(1, $this->metrics->totalFor('auth.login_failed'));
    }

    public function testRegisteredEventEmitsSignupCounter(): void
    {
        $user = User::factory()->create();
        Event::dispatch(new Registered($user));

        $this->assertSame(1, $this->metrics->totalFor('auth.signup'));
    }

    public function testVerifiedEventEmitsEmailVerifiedCounter(): void
    {
        $user = User::factory()->create();
        Event::dispatch(new Verified($user));

        $this->assertSame(1, $this->metrics->totalFor('auth.email_verified'));
    }

    public function testPublicationCreatedEmitsCounter(): void
    {
        Publication::factory()->create();

        $this->assertSame(1, $this->metrics->totalFor('publication.created'));
    }

    public function testSubmissionCreatedEmitsCounter(): void
    {
        Submission::factory()->create();

        $this->assertSame(1, $this->metrics->totalFor('submission.created'));
    }

    public function testCountersCarryNoUserIdentifiers(): void
    {
        $user = User::factory()->create();
        Event::dispatch(new Login('web', $user, false));
        Event::dispatch(new Registered($user));

        foreach ($this->metrics->calls as $call) {
            $this->assertSame([], $call['attributes'], "metric {$call['name']} leaked attributes");
        }
    }
}

class FakeMetrics extends Metrics
{
    /** @var array<int, array{name: string, value: int|float, attributes: array<string,mixed>}> */
    public array $calls = [];

    public function __construct()
    {
        parent::__construct(true);
    }

    public function count(
        string $name,
        int|float $value = 1,
        array $attributes = [],
        ?Unit $unit = null
    ): void {
        $this->calls[] = ['name' => $name, 'value' => $value, 'attributes' => $attributes];
    }

    public function gauge(
        string $name,
        int|float $value,
        array $attributes = [],
        ?Unit $unit = null
    ): void {
        $this->calls[] = ['name' => $name, 'value' => $value, 'attributes' => $attributes];
    }

    public function distribution(
        string $name,
        int|float $value,
        array $attributes = [],
        ?Unit $unit = null
    ): void {
        $this->calls[] = ['name' => $name, 'value' => $value, 'attributes' => $attributes];
    }

    public function flush(): void
    {
    }

    public function totalFor(string $name): int
    {
        return count(array_filter($this->calls, fn ($c) => $c['name'] === $name));
    }
}
