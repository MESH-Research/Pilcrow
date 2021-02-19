<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class VerifyEmailTest extends TestCase
{
    use RefreshDatabase;
    use MakesGraphQLRequests;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $testNow = Carbon::create(2021, 01, 15, 12, 30, 00);
        Carbon::setTestNow($testNow);

        parent::setUp();
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
        Carbon::setTestNow(null);

        parent::tearDown();
    }

    /**
     * Call the verifyEmail graphql mutation
     *
     * @param array $variables
     * @return \Illuminate\Testing\TestResponse
     */
    public function callVerifyEmailEndpoint(array $variables): \Illuminate\Testing\TestResponse
    {
        return $this->graphQL('
            mutation VerifyEmail($token: String!, $expires: String!) {
                verifyEmail(token: $token, expires: $expires) {
                    id
                }
            }
        ', $variables);
    }

    /**
     * Call the sendEmailVerification graphql mutation
     *
     * @param array $variables
     * @return \Illuminate\Testing\TestResponse
     */
    public function callSendVerifyEmailEndpoint(array $variables): \Illuminate\Testing\TestResponse
    {
        return $this->graphQL('
            mutation SendEmail($id: ID) {
                sendEmailVerification(id: $id) {
                    id
                }
            }
        ', $variables);
    }

    /**
     * Call createUser graphql mutation
     *
     * @param array $variables
     * @return \Illuminate\Testing\TestResponse
     */
    public function callCreateUserEndpoint(array $variables): \Illuminate\Testing\TestResponse
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
     * @return void
     */
    public function testEmailVerificationSent(): void
    {
        Notification::fake();
        $testUser = User::factory()->make(['email' => 'mesh@msu.edu']);

        $response = $this->callCreateUserEndpoint($testUser->makeVisible('password')->attributesToArray());
        $response->assertJsonPath('data.createUser.username', $testUser->username);
        $user = User::find(Arr::get($response, 'data.createUser.id'));

        $this->assertNotNull($user);
        Notification::assertSentTo([$user], VerifyEmail::class);
    }

    /**
     * @return void
     */
    public function testEmailVerificationSentOnEmailUpdate(): void
    {
        Notification::fake();
        $testUser = User::factory()->create(['email' => 'mesh@msu.edu']);

        $testUser->email = 'mesh2@msu.edu';
        $testUser->save();

        Notification::assertSentTo([$testUser], VerifyEmail::class);
        $this->assertNull($testUser->email_verified_at);
    }

    /**
     * @return array
     */
    public function dataResendVerificationToSelf(): array
    {
        return [
            ['1'],
            [null],
        ];
    }

    /**
     * @dataProvider dataResendVerificationToSelf
     * @param string $id Data provided
     * @return void
     */
    public function testCanResendVerificationEmailToSelf(?string $id): void
    {
        Notification::fake();
        $testUser = User::factory()->create(['id' => 1, 'email' => 'mesh@msu.edu', 'email_verified_at' => null]);

        $this->actingAs($testUser);
        $response = $this->callSendVerifyEmailEndpoint(['id' => $id]);

        Notification::assertSentTo([$testUser], VerifyEmail::class);
        $response->assertJsonPath('errors', null);
    }

    /**
     * @return void
     */
    public function testAdminCanResendVerificationEmailToAnother(): void
    {
        Notification::fake();
        $testUser = User::factory()->create(['email' => 'mesh@msu.edu', 'email_verified_at' => null]);
        $adminUser = User::factory()->create(['email' => 'mesh2@msu.edu'])->assignRole(Role::APPLICATION_ADMINISTRATOR);

        $this->actingAs($adminUser);
        $response = $this->callSendVerifyEmailEndpoint(['id' => $testUser->id]);

        Notification::assertSentTo([$testUser], VerifyEmail::class);
        $response->assertJsonPath('errors', null);
    }

    /**
     * @return void
     */
    public function testCannotResendVerificationEmailToAnother(): void
    {
        Notification::fake();
        $testUser = User::factory()->create(['email' => 'mesh@msu.edu', 'email_verified_at' => null]);
        $otherUser = User::factory()->create(['email' => 'mesh2@msu.edu']);

        $this->actingAs($otherUser);
        $response = $this->callSendVerifyEmailEndpoint(['id' => $testUser->id]);

        Notification::assertNotSentTo([$testUser], VerifyEmail::class);
        $response->assertGraphQLErrorCategory('authentication');
    }

    /**
     * @return void
     */
    public function testCannotSendNotifcationWhenVerified(): void
    {
        Notification::fake();
        $testUser = User::factory()->create(['email' => 'mesh@msu.edu']);
        $testUser->markEmailAsVerified();

        $this->actingAs($testUser);
        $response = $this->callSendVerifyEmailEndpoint(['id' => $testUser->id]);

        Notification::assertNotSentTo([$testUser], VerifyEmail::class);
        $response->assertJsonPath('errors.0.extensions.code', 'VERIFY_EMAIL_VERIFIED');
    }

    /**
     * @return void
     */
    public function testCanVerifyEmail(): void
    {
        $testUser = User::factory()->create(['email' => 'mesh@msu.edu']);
        $expires = (string)Carbon::now()->addMinutes(10)->timestamp;
        $hash = $testUser->makeEmailVerificationHash($expires);

        $this->actingAs($testUser);
        $response = $this->callVerifyEmailEndpoint(['token' => $hash, 'expires' => $expires]);

        $this->assertNotNull(Arr::get($response, 'data.verifyEmail'));
        $this->assertNull(Arr::get($response, 'errors'));
    }

    /**
     * @return void
     */
    public function testExpiredTokenFailsEmailVerification(): void
    {
        $testUser = User::factory()->create(['email' => 'mesh@msu.edu']);
        $expires = (string)Carbon::now()->subMinutes(61)->timestamp;
        $hash = $testUser->makeEmailVerificationHash($expires);

        $this->actingAs($testUser);
        $response = $this->callVerifyEmailEndpoint(['token' => $hash, 'expires' => $expires]);

        $response->assertJsonPath('errors.0.extensions.code', 'VERIFY_TOKEN_EXPIRED');
    }

    /**
     * @return void
     */
    public function testInvalidTokenFailsEmailVerification(): void
    {
        $testUser = User::factory()->create(['email' => 'mesh@msu.edu']);
        $expires = (string)Carbon::now()->addMinutes(10)->timestamp;
        $hash = 'aninvalidhash';

        $this->actingAs($testUser);
        $response = $this->callVerifyEmailEndpoint(['token' => $hash, 'expires' => $expires]);

        $response->assertJsonPath('errors.0.extensions.code', 'VERIFY_TOKEN_INVALID');
    }

    /**
     * @return void
     */
    public function testExpiresCannotBeManipulated(): void
    {
        $testUser = User::factory()->create(['email' => 'mesh@msu.edu']);
        $expires = (string)Carbon::now()->subMinutes(61)->timestamp; // Create an expired hash
        $hash = $testUser->makeEmailVerificationHash($expires);

        $invalidExpires = (string)Carbon::now()->addMinutes(10)->timestamp;
        $this->actingAs($testUser);
        $response = $this->callVerifyEmailEndpoint(['token' => $hash, 'expires' => $invalidExpires]);

        $response->assertJsonPath('errors.0.extensions.code', 'VERIFY_TOKEN_INVALID');
    }
}
