<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testUniqueUsernameGenerationDoesNotDuplicateExistingUsernames()
    {
        $local_part = $this->faker->userName();
        $email = $local_part . '@msu.edu';
        $username1 = User::generateUniqueUsername($email);
        User::factory()->create([
            'email' => $email,
            'username' => $username1,
        ]);
        $email = $local_part . '@gmail.com';
        $username2 = User::generateUniqueUsername($email);
        $this->assertNotEquals($username1, $username2);
    }

    /**
     * @return void
     */
    public function testStagedUserCreation()
    {
        $user = User::createStagedUser($this->faker->email());
        $this->assertEquals(1, $user->staged);
    }

    // /**
    //  * @return void
    //  */
    // public function testInvitationCreation()
    // {
    //     $invite = Invitation::create(['email' => $this->faker->email()]);
    //     print_r($invite->toArray());
    //     $this->assertEquals(36, strlen($invite->token));
    //     $this->assertNotNull($invite->expiration);
    // }
}
