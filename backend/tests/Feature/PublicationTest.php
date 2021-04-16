<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Publication;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class PublicationTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    public function testPublicationsCanBeCreatedWithCustomNamesThatAreNotDuplicates()
    {
        $publication = Publication::factory()->create(['name' => 'Custom Name']);
        $this->assertEquals($publication->name, 'Custom Name');

        $this->expectException(QueryException::class);
        Publication::factory()->create(['name' => 'Custom Name']);
    }

    public function testPublicationsCanBeCreatedViaMutationByAnApplicationAdministrator()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->actingAs($user);
        $response = $this->graphQL(
            'mutation CreatePublication {
                createPublication(publication:{name:"Custom Publication for Unit Testing"}) {
                    id
                    name
                }
            }'
        );
        $expected_data = [
            'createPublication' => [
                'id' => '3',
                'name' => 'Custom Publication for Unit Testing'
            ]
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    public function testPublicationsCannotBeCreatedViaMutationByARegularUser()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->graphQL(
            'mutation CreatePublication {
                createPublication(publication:{name:"Custom Publication for Unit Testing"}) {
                    id
                    name
                }
            }'
        );
        $response->assertJsonPath('data', null);
    }
}
