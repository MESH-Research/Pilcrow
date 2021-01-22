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
     * Test that the name supplied to the createUser migration can be empty.
     *
     * @return void
     */
    public function testEmptyName()
    {
        $response = $this->graphQL('
            mutation {
                createUser(user: {name: "", email: "brandnew@gmail.com", password: "KajSu8viptUrz&", username: "testusername"}) {
                    name
                    username
                }
            }
        ');

        $response->assertJsonPath("data.createUser.username", "testusername");
        $response->assertJsonPath("data.createUser.name", "");
    }

    /**
     * Test that the name supplied to the createUser migration can be null.
     *
     * @return void
     */
    public function testMissingName()
    {
        $response = $this->graphQL('
            mutation {
                createUser(user: {email: "brandnew@gmail.com", password: "KajSu8viptUrz&", username: "testusername"}) {
                    name
                    username
                }
            }
        ');

        $response->assertJsonPath("data.createUser.username", "testusername");
        $response->assertJsonPath("data.createUser.name", null);
    }
}
