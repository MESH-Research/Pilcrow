<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @return array
     */
    public static function rolesAndExpectationsProvider()
    {
        return [
            'admin' => [
                'role_ids' => [1, 2, 3, 4, 5, 6],
                'expected' => 1,
            ],
            'pub admin' => [
                'role_ids' => [2, 3, 4, 5, 6],
                'expected' => 2,
            ],
            'editor' => [
                'role_ids' => [3, 4, 5, 6],
                'expected' => 3,
            ],
            'review coordinator' => [
                'role_ids' => [4, 5, 6],
                'expected' => 4,
            ],
            'reviewer' => [
                'role_ids' => [5, 6],
                'expected' => 5,
            ],
            'submitter' => [
                'role_ids' => [6],
                'expected' => 6,
            ],
            'nothing' => [
                'role_ids' => [],
                'expected' => null,
            ],
        ];
    }

    /**
     * @param User $user
     * @param string $role
     * @return void
     */
    private function assignSubmissionRole($user, $role)
    {
        $publication = Publication::factory()->create();
        Submission::factory()
            ->count(10)
            ->hasAttached($user, [], $role)
            ->for($publication)
            ->create();
    }

    /**
     * @param User $user
     * @param string $role
     * @return void
     */
    private function assignPublicationRole($user, $role)
    {
        $publication = Publication::factory()->create();
        if ($role == 'publicationAdmins') {
            $publication->publicationAdmins()->save($user);
        } elseif ($role == 'editors') {
            $publication->editors()->save($user);
        }
    }

    /**
     * @return void
     */
    #[DataProvider('rolesAndExpectationsProvider')]
    public function testHighestPrivilegedRole($role_ids, $expected)
    {
        /** @var User $user */
        $user = User::factory()->create();

        if (in_array(1, $role_ids)) {
            $user->assignRole(Role::APPLICATION_ADMINISTRATOR);
        }
        if (in_array(2, $role_ids)) {
            $this->assignPublicationRole($user, 'publicationAdmins');
        }
        if (in_array(3, $role_ids)) {
            $this->assignPublicationRole($user, 'editors');
        }
        if (in_array(4, $role_ids)) {
            $this->assignSubmissionRole($user, 'reviewCoordinators');
        }
        if (in_array(5, $role_ids)) {
            $this->assignSubmissionRole($user, 'reviewers');
        }
        if (in_array(6, $role_ids)) {
            $this->assignSubmissionRole($user, 'submitters');
        }
        $this->assertEquals($user->getHighestPrivilegedRole(), $expected);
    }

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

    /**
     * @return void
     */
    public function testUniqueUsernameGenerationWithNoEmail()
    {
        $username1 = User::generateUniqueUsername('');
        User::factory()->create([
            'email' => 'testEmail@msu.edu',
            'username' => $username1,
        ]);
        $username2 = User::generateUniqueUsername('');
        $this->assertNotEmpty($username1);
        $this->assertNotEmpty($username2);
        $this->assertNotEquals($username1, $username2);
    }
}
