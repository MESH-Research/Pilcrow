<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\ApiTestCase;

class UpdateUserMutationTest extends ApiTestCase
{
    use RefreshDatabase;

    public static function urlProvider(): array
    {
        return [
            'valid URL' => [
                'valid' => true,
                'url' => 'https://www.msu.edu',
            ],
            'invalid URL' => [
                'valid' => false,
                'url' => 'msu',
            ],
            '<script> tag in URL' => [
                'valid' => false,
                'url' => '<script>alert("hi")</script>https://www.msu.edu',
            ]
        ];
    }
    /**
     * @param bool $valid
     * @param mixed $url
     * @return void
     */
    #[DataProvider('urlProvider')]
    public function testWebsitesFieldIsValidated(bool $valid, string $url): void
    {
        $user = User::factory()->create([
            'email' => 'brandnew@gmail.com',
            'username' => 'testusername',
        ]);

        /** @var User $user */
        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation updateUserWebsites ($id: ID!, $url: String){
                updateUser(
                    user: {
                        id: $id,
                        profile_metadata: {
                            websites: [$url]
                        }
                    }
                ) {
                    id
                    profile_metadata {
                        websites
                    }
                }
            }',
            [
                'id' => $user->id,
                'url' => $url,
            ]
        );

        if (!$valid) {
            $response
                ->assertGraphQLValidationError(
                    'user.profile_metadata.websites.0',
                    'The URL is invalid',
                    "Validation error should be present for value: {$url}"
                );
        } else {
            $response->assertJsonPath(
                'data.updateUser.profile_metadata.websites.0',
                $url,
                "The Website URL should be updated to {$url}"
            );
        }
    }

    /**
     * @return void
     */
    public function testUserCanUpdateOwnData(): void
    {
        $user = User::factory()->create([
            'name' => 'test',
            'email' => 'brandnew@gmail.com',
            'username' => 'testusername',
            'profile_metadata' => [
                'specialization' => '1',
            ],
        ]);

        /** @var User $user */
        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation updateUser ($id: ID!){
                updateUser(
                    user: {
                        id: $id,
                        username: "testbrandnewusername",
                        profile_metadata: {
                            specialization: "2"
                        }
                    }
                ) {
                    username
                    profile_metadata {
                        specialization
                    }
                }
            }',
            [
                'id' => $user->id,
            ]
        );

        $response->assertJsonPath('data.updateUser.username', 'testbrandnewusername');
        $response->assertJsonPath('data.updateUser.profile_metadata.specialization', '2');
    }

    public function testUserCanUpdateOwnDataToBeTheSame()
    {
        $user = User::factory()->create([
            'name' => 'testname',
            'email' => 'brandnew@gmail.com',
            'username' => 'testusername',
            'profile_metadata' => [
                'specialization' => '1',
            ],
        ]);

        /** @var User $user */
        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation updateUser ($id: ID!){
                updateUser(
                    user: {
                        id: $id,
                        name: "testname",
                        email: "brandnew@gmail.com",
                        username: "testusername",
                        profile_metadata: {
                            specialization: "1"
                        }
                    }
                ) {
                    name
                    email
                    username
                    profile_metadata {
                        specialization
                    }
                }
            }',
            [
                'id' => $user->id,
            ]
        );
        $response->assertJsonPath('data.updateUser.name', 'testname');
        $response->assertJsonPath('data.updateUser.email', 'brandnew@gmail.com');
        $response->assertJsonPath('data.updateUser.username', 'testusername');
        $response->assertJsonPath('data.updateUser.profile_metadata.specialization', '1');
    }

    /**
     * @return void
     */
    public function testUserCannotUpdateOthersData(): void
    {
        /** @var User $loggedInUser */
        $loggedInUser = User::factory()->create([
            'email' => 'loggedin@gmail.com',
            'username' => 'loggedinuser',
        ]);

        $userToUpdate = User::factory()->create([
            'email' => 'usertoupdate@gmail.com',
            'username' => 'usertoupdate',
            'profile_metadata' => [
                'specialization' => '1',
            ],
        ]);

        $this->actingAs($loggedInUser);

        $response = $this->graphQL(
            'mutation updateUser ($id: ID!){
                updateUser(
                    user: {
                        id: $id,
                        username: "testbrandnewusername",
                        profile_metadata {
                            specialization: "2"
                        }
                    }
                ) {
                    username
                    profile_metadata {
                        specialization
                    }
                }
            }',
            [
                'id' => $userToUpdate->id,
            ]
        );

        $response->assertJsonPath('data', null);
    }

    /**
     * @return void
     */
    public function testApplicationAdministratorCanUpdateOthersData(): void
    {
        $this->beAppAdmin();

        $userToUpdate = User::factory()->create([
            'email' => 'usertoupdate@gmail.com',
            'username' => 'usertoupdate',
            'profile_metadata' => [
                'specialization' => '1',
            ],
        ]);
        $response = $this->graphQL(
            'mutation updateUser ($id: ID!){
                updateUser(
                    user: {
                        id: $id,
                        username: "testbrandnewusername",
                        profile_metadata: {
                            specialization: "2"
                        }
                    }
                ) {
                    username
                    profile_metadata {
                        specialization
                    }
                }
            }',
            [
                'id' => $userToUpdate->id,
            ]
        );
        $response->assertJsonPath('data.updateUser.username', 'testbrandnewusername');
        $response->assertJsonPath('data.updateUser.profile_metadata.specialization', '2');
    }

    public function testApplicationAdministratorCanUpdateOthersDataToBeTheSame(): void
    {
        $this->beAppAdmin();

        $userToUpdate = User::factory()->create([
            'name' => 'testname',
            'email' => 'testemail@gmail.com',
            'username' => 'testusername',
            'profile_metadata' => [
                'specialization' => '1',
            ],
        ]);
        $response = $this->graphQL(
            'mutation updateUser ($id: ID!){
                updateUser(
                    user: {
                        id: $id,
                        name: "testname",
                        email: "testemail@gmail.com",
                        username: "testusername",
                        profile_metadata: {
                            specialization: "2"
                        }
                    }
                ) {
                    name
                    email
                    username
                    profile_metadata {
                        specialization
                    }
                }
            }',
            [
                'id' => $userToUpdate->id,
            ]
        );
        $response->assertJsonPath('data.updateUser.name', 'testname');
        $response->assertJsonPath('data.updateUser.email', 'testemail@gmail.com');
        $response->assertJsonPath('data.updateUser.username', 'testusername');
        $response->assertJsonPath('data.updateUser.profile_metadata.specialization', '2');
    }
}
