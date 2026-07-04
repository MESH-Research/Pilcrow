<?php
declare(strict_types=1);

namespace App\Telemetry;

use Sentry\Unit;
use function Sentry\traceMetrics;

/**
 * Thin wrapper around the Sentry PHP SDK trace-metrics module. All methods are
 * no-ops when telemetry is disabled, so call sites do not need feature flags.
 */
class Metrics
{
    /**
     * @param bool $enabled when false, all emit methods short-circuit to no-ops
     */
    public function __construct(private readonly bool $enabled)
    {
    }

    /**
     * @return bool true when telemetry is configured and metrics will be emitted
     */
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

        traceMetrics()->count($name, $value, $attributes, $unit);
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

        traceMetrics()->gauge($name, $value, $attributes, $unit);
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

        traceMetrics()->distribution($name, $value, $attributes, $unit);
    }

    /**
     * Flush any buffered metrics to the transport. Safe to call when disabled.
     */
    public function flush(): void
    {
        if (! $this->enabled) {
            return;
        }

        traceMetrics()->flush();
    }
}
