<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Http\Middleware\IntegrationTestingMiddleware;
use App\IntegrationTesting\TableSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Tests\ApiTestCase;

class TestIsolationTest extends ApiTestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        DB::connection()->setTablePrefix('');
        parent::tearDown();
    }

    public function test_prefix_is_deterministic(): void
    {
        $this->assertEquals(
            TableSnapshot::prefix('same'),
            TableSnapshot::prefix('same')
        );
        $this->assertNotEquals(
            TableSnapshot::prefix('a'),
            TableSnapshot::prefix('b')
        );
    }

    public function test_middleware_does_nothing_without_token(): void
    {
        $middleware = new IntegrationTestingMiddleware();
        $request = Request::create('/graphql', 'POST');

        $prefixDuringRequest = 'not-set';

        $middleware->handle($request, function () use (&$prefixDuringRequest) {
            $prefixDuringRequest = DB::connection()->getTablePrefix();

            return new Response();
        });

        $this->assertEquals('', $prefixDuringRequest);
    }

    public function test_middleware_does_nothing_in_production(): void
    {
        App::detectEnvironment(fn() => 'production');

        $middleware = new IntegrationTestingMiddleware();
        $request = Request::create('/graphql', 'POST');
        $request->headers->set('X-Test-Token', 'some-token');

        $prefixDuringRequest = 'not-set';

        $middleware->handle($request, function () use (&$prefixDuringRequest) {
            $prefixDuringRequest = DB::connection()->getTablePrefix();

            return new Response();
        });

        $this->assertEquals('', $prefixDuringRequest);
    }

    public function test_middleware_skips_prefix_when_no_shadow(): void
    {
        $middleware = new IntegrationTestingMiddleware();
        $request = Request::create('/graphql', 'POST');
        $request->headers->set('X-Test-Token', 'nonexistent-token');

        $prefixDuringRequest = 'not-set';

        $middleware->handle($request, function () use (&$prefixDuringRequest) {
            $prefixDuringRequest = DB::connection()->getTablePrefix();

            return new Response();
        });

        $this->assertEquals('', $prefixDuringRequest);
    }

    public function test_middleware_resets_prefix_after_request(): void
    {
        // Simulate a stale prefix from a previous request
        DB::connection()->setTablePrefix('_stale_prefix_');

        $middleware = new IntegrationTestingMiddleware();
        $request = Request::create('/graphql', 'POST');
        $request->headers->set('X-Test-Token', 'any-token');

        $middleware->handle($request, fn() => new Response());

        $this->assertEquals('', DB::connection()->getTablePrefix());
    }

    public function test_middleware_resets_stale_prefix_for_tokenless_request(): void
    {
        DB::connection()->setTablePrefix('_stale_prefix_');

        $middleware = new IntegrationTestingMiddleware();
        $request = Request::create('/graphql', 'POST');
        // No X-Test-Token header

        $prefixDuringRequest = 'not-set';

        $middleware->handle($request, function () use (&$prefixDuringRequest) {
            $prefixDuringRequest = DB::connection()->getTablePrefix();

            return new Response();
        });

        $this->assertEquals('', $prefixDuringRequest);
    }

    public function test_full_reset_available_in_testing(): void
    {
        $response = $this->graphQL('mutation { resetDatabase }');
        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['resetDatabase']]);
    }

    public function test_full_reset_rejects_production(): void
    {
        App::detectEnvironment(fn() => 'production');

        $response = $this->graphQL('mutation { resetDatabase }');
        $response->assertGraphQLErrorMessage('Internal server error');
    }
}
