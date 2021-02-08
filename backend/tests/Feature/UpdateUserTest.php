<?php

namespace Tests\Feature;

use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateUserTest extends TestCase
{
    use MakesGraphQLRequests, RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testUserUpdate()
    {
        $response = $this->graphQL(
            'mutation {
                createUser(user: {
                    name: "brandnew", 
                    email: "brandnew@gmail.com", 
                    password: "KajSu8viptUrz&", 
                    username: "testusername"
                }) {
                    id
                    name
                    username
                }
            }'
        );

        $response->assertJsonPath("data.createUser.username", "testusername");
        $response->assertJsonPath("data.createUser.name", "brandnew");

        $id = $response['data']['createUser']['id'];

        $response = $this->graphQL(
            'mutation updateUser ($id: ID!){
                updateUser(id: $id,
                    user: {
                        username: "testbrandnewusername"
                        }
                    ) {
                    username
                }
            }', ['id' => $id]
        );
        $response->assertJsonPath("data.updateUser.username", "testbrandnewusername");
    }
}
