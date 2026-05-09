<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class LoginMutationTest extends ApiTestCase
{
    use RefreshDatabase;

    public function testLoginReturnsViewerEmail(): void
    {
        $user = User::factory()->create([
            'email' => 'login-visible@example.test',
        ]);

        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation Login($email: String!, $password: String!) {
                login(email: $email, password: $password) {
                    id
                    email
                }
            }',
            [
                'email' => $user->email,
                'password' => 'password',
            ]
        );

        $response->assertJson([
            'data' => [
                'login' => [
                    'id' => (string) $user->id,
                    'email' => 'login-visible@example.test',
                ],
            ],
        ]);
    }
}
