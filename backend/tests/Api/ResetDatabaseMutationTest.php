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
    public function testFullResetAvailableInTestingEnvironment(): void
    {
        $this->assertEquals('testing', App::environment());

        $response = $this->graphQL(
            'mutation { resetDatabase }'
        );

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['resetDatabase']]);
    }

    public function testFullResetResolverRejectsProduction(): void
    {
        App::detectEnvironment(fn() => 'production');
        $this->assertEquals('production', App::environment());

        $response = $this->graphQL(
            'mutation { resetDatabase }'
        );

        $response->assertGraphQLErrorMessage('Internal server error');
        $response->assertJsonPath(
            'errors.0.extensions.debugMessage',
            'Reset database is not available in this environment.'
        );
    }
}
