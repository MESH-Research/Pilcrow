<?php
declare(strict_types=1);

namespace Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\ApiTestCase;

class ResetDatabaseMutationTest extends ApiTestCase
{
    use RefreshDatabase;

    /**
     * Test that the resetDatabase mutation is available in the testing environment.
     *
     * @return void
     */
    public function testResetDatabaseAvailableInTestingEnvironment(): void
    {
        $this->assertEquals('testing', App::environment());

        $response = $this->graphQL(
            'mutation { resetDatabase }'
        );

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['resetDatabase']]);
    }

    /**
     * Test that the resetDatabase mutation resolver rejects non-local environments.
     *
     * Note: This tests the runtime guard in the ResetDatabase resolver.
     * The schema is already stitched during boot (in 'testing' mode), so
     * detectEnvironment only affects the resolver's environment check.
     *
     * @return void
     */
    public function testResetDatabaseResolverRejectsProduction(): void
    {
        App::detectEnvironment(fn() => 'production');
        $this->assertEquals('production', App::environment());

        $response = $this->graphQL(
            'mutation { resetDatabase }'
        );

        $response->assertGraphQLErrorMessage('Internal server error');
        $response->assertJsonPath(
            'errors.0.extensions.debugMessage',
            'Database reset is not available in this environment.'
        );
    }
}
