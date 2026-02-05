<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class PersonalAccessTokenTest extends ApiTestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testUnauthenticatedUserCannotCreateToken(): void
    {
        $response = $this->graphQL(
            'mutation CreateToken($name: String!) {
                createPersonalAccessToken(name: $name) {
                    token
                    personalAccessToken {
                        id
                        name
                    }
                }
            }',
            ['name' => 'Test Token']
        );

        $response->assertGraphQLErrorMessage('Unauthenticated.');
    }

    /**
     * @return void
     */
    public function testAuthenticatedUserCanCreateToken(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation CreateToken($name: String!) {
                createPersonalAccessToken(name: $name) {
                    token
                    personalAccessToken {
                        id
                        name
                    }
                }
            }',
            ['name' => 'My API Token']
        );

        $response->assertJsonStructure([
            'data' => [
                'createPersonalAccessToken' => [
                    'token',
                    'personalAccessToken' => [
                        'id',
                        'name',
                    ],
                ],
            ],
        ]);

        $response->assertJsonPath('data.createPersonalAccessToken.personalAccessToken.name', 'My API Token');

        // Token should be in Sanctum format: {id}|{hash}
        $token = $response->json('data.createPersonalAccessToken.token');
        $this->assertMatchesRegularExpression('/^\d+\|.+$/', $token);

        // Token should be saved in database
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'My API Token',
        ]);
    }

    /**
     * @return void
     */
    public function testUnauthenticatedUserCannotListTokens(): void
    {
        $response = $this->graphQL(
            'query {
                currentUser {
                    tokens {
                        id
                        name
                    }
                }
            }'
        );

        // currentUser returns null when unauthenticated
        $response->assertJsonPath('data.currentUser', null);
    }

    /**
     * @return void
     */
    public function testAuthenticatedUserCanListTheirTokens(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create some tokens
        $user->createToken('Token 1');
        $user->createToken('Token 2');
        $user->createToken('Token 3');

        $response = $this->graphQL(
            'query {
                currentUser {
                    tokens {
                        id
                        name
                        created_at
                    }
                }
            }'
        );

        $response->assertJsonCount(3, 'data.currentUser.tokens');

        $names = collect($response->json('data.currentUser.tokens'))->pluck('name')->toArray();
        $this->assertContains('Token 1', $names);
        $this->assertContains('Token 2', $names);
        $this->assertContains('Token 3', $names);
    }

    /**
     * @return void
     */
    public function testUserCanOnlySeeTheirOwnTokens(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // User 1 creates tokens
        $user1->createToken('User 1 Token');

        // User 2 creates tokens
        $user2->createToken('User 2 Token');

        // Acting as user 1
        $this->actingAs($user1);

        $response = $this->graphQL(
            'query {
                currentUser {
                    tokens {
                        id
                        name
                    }
                }
            }'
        );

        $response->assertJsonCount(1, 'data.currentUser.tokens');
        $response->assertJsonPath('data.currentUser.tokens.0.name', 'User 1 Token');
    }

    /**
     * @return void
     */
    public function testUnauthenticatedUserCannotRevokeToken(): void
    {
        $response = $this->graphQL(
            'mutation RevokeToken($id: ID!) {
                revokePersonalAccessToken(id: $id)
            }',
            ['id' => '1']
        );

        $response->assertGraphQLErrorMessage('Unauthenticated.');
    }

    /**
     * @return void
     */
    public function testAuthenticatedUserCanRevokeTheirToken(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $token = $user->createToken('Token to revoke');
        $tokenId = $token->accessToken->id;

        $response = $this->graphQL(
            'mutation RevokeToken($id: ID!) {
                revokePersonalAccessToken(id: $id)
            }',
            ['id' => (string) $tokenId]
        );

        $response->assertJsonPath('data.revokePersonalAccessToken', true);

        // Token should be deleted from database
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }

    /**
     * @return void
     */
    public function testUserCannotRevokeAnotherUsersToken(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // User 1 creates a token
        $token = $user1->createToken('User 1 Token');
        $tokenId = $token->accessToken->id;

        // Acting as user 2
        $this->actingAs($user2);

        $response = $this->graphQL(
            'mutation RevokeToken($id: ID!) {
                revokePersonalAccessToken(id: $id)
            }',
            ['id' => (string) $tokenId]
        );

        // Should return false (token not found for this user)
        $response->assertJsonPath('data.revokePersonalAccessToken', false);

        // Token should still exist
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }

    /**
     * @return void
     */
    public function testRevokeNonExistentTokenReturnsFalse(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation RevokeToken($id: ID!) {
                revokePersonalAccessToken(id: $id)
            }',
            ['id' => '99999']
        );

        $response->assertJsonPath('data.revokePersonalAccessToken', false);
    }

    /**
     * @return void
     */
    public function testCreatedTokenCanBeUsedForAuthentication(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('Auth Test Token');
        $plainToken = $token->plainTextToken;

        // Use the token to authenticate a request
        $response = $this->withHeader('Authorization', 'Bearer ' . $plainToken)
            ->graphQL(
                'query {
                    currentUser {
                        id
                        email
                    }
                }'
            );

        $response->assertJsonPath('data.currentUser.id', (string) $user->id);
        $response->assertJsonPath('data.currentUser.email', $user->email);
    }
}
