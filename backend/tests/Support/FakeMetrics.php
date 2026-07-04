<?php
declare(strict_types=1);

namespace Tests\Support;

use App\Telemetry\Metrics;
use Sentry\Unit;

/**
 * Test double that records emit calls in-memory instead of forwarding to the
 * Sentry SDK. Bind into the container with `$this->app->instance(Metrics::class, new FakeMetrics())`.
 */
class FakeMetrics extends Metrics
{
    /**
     * @var array<int, array{name: string, value: int|float, attributes: array<string,mixed>}>
     */
    public array $calls = [];

    public function __construct()
    {
        parent::__construct(true);
    }

    /**
     * @param array<string, int|float|string|bool> $attributes
     */
    public function count(
        string $name,
        int|float $value = 1,
        array $attributes = [],
        ?Unit $unit = null
    ): void {
        unset($unit);
        $this->calls[] = ['name' => $name, 'value' => $value, 'attributes' => $attributes];
    }

    /**
     * @param array<string, int|float|string|bool> $attributes
     */
    public function gauge(
        string $name,
        int|float $value,
        array $attributes = [],
        ?Unit $unit = null
    ): void {
        unset($unit);
        $this->calls[] = ['name' => $name, 'value' => $value, 'attributes' => $attributes];
    }

    /**
     * @param array<string, int|float|string|bool> $attributes
     */
    public function distribution(
        string $name,
        int|float $value,
        array $attributes = [],
        ?Unit $unit = null
    ): void {
        unset($unit);
        $this->calls[] = ['name' => $name, 'value' => $value, 'attributes' => $attributes];
    }

    public function flush(): void
    {
    }

    /**
     * Return the number of times `count()` was invoked with the given metric name.
     */
    public function totalFor(string $name): int
    {
        return count(array_filter($this->calls, fn($c) => $c['name'] === $name));
    }
}
