<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class CreateUserMutationTest extends ApiTestCase
{
    use RefreshDatabase;

    /**
     * Call GraphQL endpoint to create a user
     *
     * @param array $variables Contains user details to save to DB for testing.
     * @return \Illuminate\Testing\TestResponse
     */
    public function callEndpoint(array $variables): \Illuminate\Testing\TestResponse
    {
        return $this->graphQL(
            'mutation CreateUser($username: String! $email: String! $name: String $password: String!) {
                createUser(user: { username: $username email: $email name: $name password: $password}) {
                    username
                    id
                    name
                    email
                }
            }',
            $variables
        );
    }

    /**
     * @return array
     */
    public function nameValidationProvider(): array
    {
        return [
            ['', false],
            [null, false],
            [str_repeat('*', 255), '/^Validation failed/'],
        ];
    }

    /**
     * @dataProvider nameValidationProvider
     * @param string $name Name value to test
     * @param mixed $failure Expected failure category or false if no failure expected
     */
    public function nameValidation(?string $name, $failure): void
    {
        $testUser = User::factory()->realEmailDomain()->make(['name' => $name]);
        $response = $this->callEndpoint($testUser->makeVisible('password')->attributesToArray());

        if ($failure) {
            $this->assertStringStartsWith('Validation failed', $response->json('errors.0.message'));
        } else {
            $response->assertJsonPath('data.createUser.name', $name);
        }
    }

    /**
     * @return array
     */
    public static function usernameValidationProvider(): array
    {
        return [
            ['', '/must not be null/'],
            [null, '/must not be null/'],
            ['mytestuser', false],
            ['duplicateUser', '/^Validation failed/'],
        ];
    }

    /**
     * @dataProvider usernameValidationProvider
     * @param string $username Username value to test
     * @param mixed $failure Expected failure category or false if no failure expected
     * @return void
     */
    public function testUsernameValidation(?string $username, $failure): void
    {
        User::factory()->create(['username' => 'duplicateUser']);

        $testUser = User::factory()->realEmailDomain()->make(['username' => $username]);
        $response = $this->callEndpoint($testUser->makeVisible('password')->attributesToArray());

        if ($failure) {
            $this->assertMatchesRegularExpression($failure, $response->json('errors.0.message'));
        } else {
            $response->assertJsonPath('data.createUser.username', $username);
        }
    }

    /**
     * @return array
     */
    public static function emailValidationProvider(): array
    {
        return [
            ['adamsb@msu.edu', false],
            ['notanemail', '/^Validation failed/'],
            ['', '/must not be null/'],
            [null, '/must not be null/'],
            ['dupeemail@meshresearch.net', '/^Validation failed/'],
            ['nodomain@example.com', '/^Validation failed/'],
        ];
    }

    /**
     * @dataProvider emailValidationProvider
     * @param string $email Email value to test
     * @param mixed $failure Expected failure category or false if no failure expected
     * @return void
     */
    public function testEmailValidation(?string $email, $failure): void
    {
        User::factory()->create(['email' => 'dupeemail@meshresearch.net']);

        $testUser = User::factory()->make(['email' => $email]);
        $response = $this->callEndpoint($testUser->makeVisible('password')->attributesToArray());

        if ($failure) {
            $this->assertMatchesRegularExpression($failure, $response->json('errors.0.message'));
        } else {
            $response->assertJsonPath('data.createUser.email', $email);
        }
    }

    /**
     * @return array
     */
    public static function passwordValidationProvider(): array
    {
        return [
            ['password', '/^Validation failed/'],
            ['', '/must not be null/'],
            ['qwerty', '/^Validation failed/'],
            [null, '/must not be null/'],
            ['coob!DrijAr5oc', false],
        ];
    }

    /**
     * @dataProvider passwordValidationProvider
     * @param string $password Password value to test
     * @param mixed $failure Expected failure category or false if no failure expected
     * @return void
     */
    public function testPasswordValidation(?string $password, $failure): void
    {
        $testUser = User::factory()->realEmailDomain()->make(['password' => $password]);
        $response = $this->callEndpoint($testUser->makeVisible('password')->attributesToArray());

        if ($failure) {
            $this->assertMatchesRegularExpression($failure, $response->json('errors.0.message'));
        } else {
            $response->assertGraphQLValidationPasses();
        }
    }
}
