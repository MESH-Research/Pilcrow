<?php

namespace Tests\Feature;

use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateUserEndpointTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testEmptyName()
    {
        $response = $this->graphQL('
        
            mutation {
                createUser(user: {name: "", email: "brandnew@gmail.com", password: "KajSu8viptUrz&", username: "testusername"}) {
                    username
                }
            }
        ');

        $response->assertJsonPath("data.createUser.username", "testusername");


    }

    public function testMissingName()
    {
        $response = $this->graphQL('
        
            mutation {
                createUser(user: {email: "brandnew@gmail.com", password: "KajSu8viptUrz&", username: "testusername"}) {
                    username
                }
            }
        ');

        $response->assertJsonPath("data.createUser.username", "testusername");


    }
}
