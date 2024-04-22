<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class UpdateUserMutationTest extends ApiTestCase
{
    use RefreshDatabase;

    /**
     * @return array
     */
    public static function urlProvider()
    {
        $invalid = 'The URL is invalid';
        $missing = 'The user.profile_metadata.websites.0 field must have a value.';

        return [
            'null' => [null,$missing],
            'empty' => ['',$missing],
            'msu' => ['msu',$invalid],
            'msu.' => ['msu.',$invalid],
            'msu.edu' => ['msu.edu'],
            'msu.edu/' => ['msu.edu/'],
            'www' => ['www',$invalid],
            'www.' => ['www.',$invalid],
            'www.msu' => ['www.msu',$invalid],
            'www.msu.' => ['www.msu.',$invalid],
            'www.msu.e' => ['www.msu.e',$invalid],
            'www.msu.ed' => ['www.msu.ed',$invalid],
            'www.msu.edu' => ['www.msu.edu'],
            'www.msu.edu/' => ['www.msu.edu/'],
            'http' => ['http',$invalid],
            'http:' => ['http:',$invalid],
            'http:/' => ['http:/',$invalid],
            'http://' => ['http://',$invalid],
            'http://msu' => ['http://msu'],
            'http://msu.' => ['http://msu.'],
            'http://msu.e' => ['http://msu.e'],
            'http://msu.ed' => ['http://msu.ed'],
            'http://msu.edu' => ['http://msu.edu'],
            'http://msu.edu/' => ['http://msu.edu/'],
            'https://cal.msu.edu' => ['https://cal.msu.edu/'],
            'go-gle.co' => ['go-gle.co'],
            'console.log("hi")' => ['console.log("hi")',$invalid],
            "<script>alert('hi')</script>google.com/" => ["<script>alert('hi')</script>google.com/",'','google.com/'],
            "<script>alert('hi')</script>google.com/about" => ["<script>alert('hi')</script>google.com/about",'','google.com/about'],
            "<script>alert('hi')</script>google.com" => ["<script>alert('hi')</script>google.com",'','google.com'],
            "<script>alert('hi')</script>http://google.com" => ["<script>alert('hi')</script>http://google.com",'','http://google.com'],
            "<script>alert('hi')</script>http://google.com/" => ["<script>alert('hi')</script>http://google.com/",'','http://google.com/'],
            "<script>alert('hi')</script>http://google.com/about" => ["<script>alert('hi')</script>http://google.com/about",'','http://google.com/about'],
            "<script>alert('hi')</script>https://google.com" => ["<script>alert('hi')</script>https://google.com",'','https://google.com'],
            "<script>alert('hi')</script>https://google.com/" => ["<script>alert('hi')</script>https://google.com/",'','https://google.com/'],
            "<script>alert('hi')</script>https://google.com/about" => ["<script>alert('hi')</script>https://google.com/about",'','https://google.com/about'],
            "javascript:alert('hi')" => ["javascript:alert('hi')",$invalid],
            'google.<script>alert("Test Alert")</script>' => ['google.<script>alert("Test Alert")</script>',$invalid],
            'eval()' => ['eval()',$invalid],
            'Function()' => ['Function()',$invalid],
            'setTimeout()' => ['setTimeout()',$invalid],
            'setInterval()' => ['setInterval()',$invalid],
            'setImmediate()' => ['setImmediate()',$invalid],
            'execCommand()' => ['execCommand()',$invalid],
            'execScript()' => ['execScript()',$invalid],
            'msSetImmediate()' => ['msSetImmediate()',$invalid],
            'range.createContextualFragment()' => ['range.createContextualFragment()',$invalid],
            'crypto.generateCRMFRequest()' => ['crypto.generateCRMFRequest()',$invalid],
        ];
    }

    /**
     * @dataProvider urlProvider
     * @param mixed $url
     * @param string $error_message (optional)
     * @param string $sanitized (optional)
     * @return void
     */
    public function testUrl(mixed $url, string $error_message = '', string $sanitized = ''): void
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

        if ($error_message) {
            $response
                ->assertGraphQLErrorMessage('Validation failed for the field [updateUser].')
                ->assertGraphQLValidationError(
                    'user.profile_metadata.websites.0',
                    $error_message
                );
        } else {
            if ($sanitized) {
                $url = $sanitized;
            }
            $response->assertJsonPath('data.updateUser.profile_metadata.websites.0', $url);
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
