<?php
declare(strict_types=1);

namespace Tests\Unit\Telemetry;

use App\Telemetry\Metrics;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Sentry\Metrics\MetricsAggregator;
use Sentry\SentrySdk;
use Sentry\Unit;

/**
 * Exercises the conditional gate in App\Telemetry\Metrics. Verifies disabled
 * mode short-circuits without touching the SDK and enabled mode forwards to the
 * Sentry TraceMetrics aggregator. Inspects the shared singleton aggregator
 * buffer directly because the wrapper calls the global Sentry\traceMetrics()
 * helper and cannot accept an injected dispatcher.
 */
class MetricsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->resetAggregatorBuffer();
    }

    protected function tearDown(): void
    {
        $this->resetAggregatorBuffer();
        parent::tearDown();
    }

    public function testIsEnabledReflectsConstructorFlag(): void
    {
        $this->assertTrue((new Metrics(true))->isEnabled());
        $this->assertFalse((new Metrics(false))->isEnabled());
    }

    public function testDisabledCountDoesNotBufferMetric(): void
    {
        (new Metrics(false))->count('disabled.count', 5, ['k' => 'v'], Unit::second());

        $this->assertNull($this->aggregatorBuffer());
    }

    public function testDisabledGaugeDoesNotBufferMetric(): void
    {
        (new Metrics(false))->gauge('disabled.gauge', 1.5);

        $this->assertNull($this->aggregatorBuffer());
    }

    public function testDisabledDistributionDoesNotBufferMetric(): void
    {
        (new Metrics(false))->distribution('disabled.distribution', 2.0);

        $this->assertNull($this->aggregatorBuffer());
    }

    public function testDisabledFlushIsNoOp(): void
    {
        (new Metrics(false))->flush();

        $this->assertNull($this->aggregatorBuffer());
    }

    public function testEnabledCountForwardsToSentryAggregator(): void
    {
        (new Metrics(true))->count('enabled.count', 3, ['region' => 'us-east'], Unit::second());

        $buffer = $this->aggregatorBuffer();
        $this->assertNotNull($buffer);
        $this->assertSame(1, count($buffer));
    }

    public function testEnabledGaugeForwardsToSentryAggregator(): void
    {
        (new Metrics(true))->gauge('enabled.gauge', 7.5);

        $buffer = $this->aggregatorBuffer();
        $this->assertNotNull($buffer);
        $this->assertSame(1, count($buffer));
    }

    public function testEnabledDistributionForwardsToSentryAggregator(): void
    {
        (new Metrics(true))->distribution('enabled.distribution', 12.0);

        $buffer = $this->aggregatorBuffer();
        $this->assertNotNull($buffer);
        $this->assertSame(1, count($buffer));
    }

    public function testEnabledEmitsAccumulateAcrossCalls(): void
    {
        $metrics = new Metrics(true);
        $metrics->count('enabled.multi', 1);
        $metrics->gauge('enabled.multi', 2);
        $metrics->distribution('enabled.multi', 3);

        $this->assertSame(3, count($this->aggregatorBuffer()));
    }

    public function testEnabledFlushDrainsBufferedMetrics(): void
    {
        $metrics = new Metrics(true);
        $metrics->count('enabled.flush', 1);

        $this->assertSame(1, count($this->aggregatorBuffer()));

        $metrics->flush();

        $this->assertTrue($this->aggregatorBuffer()->isEmpty());
    }

    private function aggregator(): MetricsAggregator
    {
        return SentrySdk::getCurrentRuntimeContext()->getMetricsAggregator();
    }

    private function aggregatorBuffer(): mixed
    {
        $agg = $this->aggregator();
        $prop = (new ReflectionObject($agg))->getProperty('metrics');
        $prop->setAccessible(true);

        return $prop->getValue($agg);
    }

    private function resetAggregatorBuffer(): void
    {
        $agg = $this->aggregator();
        $prop = (new ReflectionObject($agg))->getProperty('metrics');
        $prop->setAccessible(true);
        $prop->setValue($agg, null);
    }
}
