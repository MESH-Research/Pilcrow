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
        return [
            'null' => [null,'/Syntax Error/'],
            'empty' => [''],
            'msu' => ['msu',false],
            'msu.' => ['msu.',false],
            'msu.edu' => ['msu.edu',true],
            'msu.edu/' => ['msu.edu/',true],
            'www' => ['www',false],
            'www.' => ['www.',false],
            'www.msu' => ['www.msu',false],
            'www.msu.' => ['www.msu.',false],
            'www.msu.e' => ['www.msu.e',false],
            'www.msu.ed' => ['www.msu.ed',false],
            'www.msu.edu' => ['www.msu.edu',true],
            'www.msu.edu/' => ['www.msu.edu/',true],
            'http' => ['http',false],
            'http:' => ['http:',false],
            'http:/' => ['http:/',false],
            'http://' => ['http://',false],
            'http://msu' => ['http://msu',true],
            'http://msu.' => ['http://msu.',true],
            'http://msu.e' => ['http://msu.e',false],
            'http://msu.ed' => ['http://msu.ed',false],
            'http://msu.edu' => ['http://msu.edu',true],
            'http://msu.edu/' => ['http://msu.edu/',true],
            'go-gle.co' => ['go-gle.co',true],
            'console.log("hi")' => ['console.log("hi")',false],
            "<script>alert('hi')</script>google.com/" => ["<script>alert('hi')</script>google.com/",false],
            "<script>alert('hi')</script>google.com/about" => ["<script>alert('hi')</script>google.com/about",false],
            "<script>alert('hi')</script>google.com" => ["<script>alert('hi')</script>google.com",false],
            "<script>alert('hi')</script>http://google.com" => ["<script>alert('hi')</script>http://google.com",false],
            "<script>alert('hi')</script>http://google.com/" => ["<script>alert('hi')</script>http://google.com/",false],
            "<script>alert('hi')</script>http://google.com/about" => ["<script>alert('hi')</script>http://google.com/about",false],
            "<script>alert('hi')</script>https://google.com" => ["<script>alert('hi')</script>https://google.com",false],
            "<script>alert('hi')</script>https://google.com/" => ["<script>alert('hi')</script>https://google.com/",false],
            "<script>alert('hi')</script>https://google.com/about" => ["<script>alert('hi')</script>https://google.com/about",false],
            "javascript:alert('hi')" => ["javascript:alert('hi')",false],
            'google.<script>alert("Hello World")</script>' => ['google.<script>alert("Hello World")</script>',false],
            'eval()' => ['eval()',false],
            'Function()' => ['Function()',false],
            'setTimeout()' => ['setTimeout()',false],
            'setInterval()' => ['setInterval()',false],
            'setImmediate()' => ['setImmediate()',false],
            'execCommand()' => ['execCommand()',false],
            'execScript()' => ['execScript()',false],
            'msSetImmediate()' => ['msSetImmediate()',false],
            'range.createContextualFragment()' => ['range.createContextualFragment()',false],
            'crypto.generateCRMFRequest()' => ['crypto.generateCRMFRequest()',false],
        ];
    }

    /**
     * @dataProvider urlProvider
     * @param mixed $url
     * @param bool $passes
     * @param string $message
     * @return void
     */
    public function testUrl(mixed $url, bool $passes, string $message = ''): void
    {
        $user = User::factory()->create([
            'email' => 'brandnew@gmail.com',
            'username' => 'testusername',
        ]);

        $response = $this->graphQL(
            'mutation updateUserWebsites ($id: ID!, $url: String!){
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
            }',
            [
                'id' => $user->id,
                'url' => $url,
            ]
        );

        if ($passes) {
            $response->assertJsonPath('data.updateUser.profile_metadata.websites[0]', $url);
        } else {
            $response->assertJsonStructure(['errors' => [['message']]]);
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
