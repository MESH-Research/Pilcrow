<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Factories\UserFactory;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateUserMigrationTest extends TestCase
{
    use MakesGraphQLRequests, RefreshDatabase;


    public function callEndpoint($variables) {
        return $this->graphQL(
            'mutation CreateUser($username: String! $email: String! $name: String $password: String!) {
                createUser(user: { username: $username email: $email name: $name password: $password}) {
                    username
                    id
                    name
                    email
                }
            }', $variables);

    }
   
    public function nameValidationProvider() {
        return [
            ['', false],
            [null, false],
            [str_repeat('*', 255), 'validation']
        ];
    }

    /**
     * @dataProvider nameValidationProvider
     */
    public function nameValidation($name, $failure)
    {
        
        $testUser = User::factory()->realEmailDomain()->make(['username' => $username]);
        $response = $this->callEndpoint($testUser->makeVisible('password')->attributesToArray());

        if ($failure) {
            $response->assertGraphQLErrorCategory($failure);
        } else {
            $response->assertJsonPath('data.createUser.name', $name);
        }
    }

    public function usernameValidationProvider() {
        return [
            ['', 'validation'],
            [null, 'graphql'],
            ['mytestuser', false],
            ['duplicateUser', 'validation'],
        ];
    }

    /**
     * @dataProvider usernameValidationProvider
     */
    public function testUsernameValidation($username, $failure)
    {
        User::factory()->create(['username' => 'duplicateUser']);
        
        $testUser = User::factory()->realEmailDomain()->make(['username' => $username]);
        $response = $this->callEndpoint($testUser->makeVisible('password')->attributesToArray());

        if ($failure) {
            $response->assertGraphQLErrorCategory($failure);
        } else {
            $response->assertJsonPath('data.createUser.username', $username);
        }
    }

    public function emailValidationProvider() {
        return [
            ['adamsb@msu.edu', false], 
            ['notanemail', 'validation'],
            ['', 'validation'],
            [null, 'validation'],
            ['dupeemail@ccrproject.dev', 'validation'],
            ['nodomain@example.com', 'validation']
        ];
    }

    /**
     * @dataProvider emailValidationProvider
     */
    public function testEmailValidation($email, $failure)
    {
        User::factory()->create(['email' => 'dupeemail@ccrproject.dev']);
        
        $testUser = User::factory()->make(['email' => $email]);
        $response = $this->callEndpoint($testUser->makeVisible('password')->attributesToArray());

        if ($failure) {
            $response->assertGraphQLErrorCategory($failure);
        } else {
            $response->assertJsonPath('data.createUser.email', $email);
        }
    }

    public function passwordValidationProvider() {
        return [
            ['password', 'validation'],
            ['', 'validation'], 
            ['qwerty', 'validation'],
            [null, 'graphql'],
            ['coob!DrijAr5oc', false] 
       ];
    }
    
    /**
     * @dataProvider passwordValidationProvider
     */
    public function testPasswordValidation($password, $failure) {
        
        $testUser = User::factory()->realEmailDomain()->make(['password' => $password]);
        $response = $this->callEndpoint($testUser->makeVisible('password')->attributesToArray());
        
        if ($failure) {
            $response->assertGraphQLErrorCategory($failure);
        } else {
            $response->assertGraphQLValidationPasses();
        }
        
    }
}
