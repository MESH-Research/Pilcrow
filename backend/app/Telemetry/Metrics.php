<?php

declare(strict_types=1);

namespace App\Telemetry;

use Sentry\Unit;

/**
 * Thin wrapper around the Sentry PHP SDK trace-metrics module. All methods are
 * no-ops when telemetry is disabled, so call sites do not need feature flags.
 */
class Metrics
{
    public function __construct(private readonly bool $enabled)
    {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
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
        if (! $this->enabled) {
            return;
        }

        \Sentry\traceMetrics()->count($name, $value, $attributes, $unit);
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
        if (! $this->enabled) {
            return;
        }

        \Sentry\traceMetrics()->gauge($name, $value, $attributes, $unit);
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
        if (! $this->enabled) {
            return;
        }

        \Sentry\traceMetrics()->distribution($name, $value, $attributes, $unit);
    }

    public function flush(): void
    {
        if (! $this->enabled) {
            return;
        }

        \Sentry\traceMetrics()->flush();
    }
}
