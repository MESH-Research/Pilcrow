<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\Role;
use App\Models\User;
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

    /**
     * @return array
     */
    public function publicationMutationProvider(): array
    {
        return [
            [
                'Custom Publication Name for Unit Testing',
                [
                    'createPublication' => [
                        'name' => 'Custom Publication Name for Unit Testing',
                    ],
                ],
            ],
            [
                '        Custom Publication with Whitespace for Unit Testing       ',
                [
                    'createPublication' => [
                        'name' => 'Custom Publication with Whitespace for Unit Testing',
                    ],
                ],
            ],
            [
                '',
                null
            ]
        ];
    }

    /**
     * @dataProvider publicationMutationProvider
     * @return void
     */
    public function testPublicationsCanBeCreatedViaMutationByAnApplicationAdministrator(mixed $publication_name, mixed $expected_data): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->actingAs($user);
        $response = $this->graphQL(
            'mutation CreatePublication ($publication_name: String) {
                createPublication(publication:{name: $publication_name}) {
                    name
                }
            }', [ 'publication_name' => $publication_name ]
        );
        $response->assertJsonPath('data', $expected_data);
    }

    public function testPublicationsCannotBeCreatedViaMutationByARegularUser()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->graphQL(
            'mutation CreatePublication {
                createPublication(publication:{name:"Custom Publication by Regular User for Unit Testing"}) {
                    id
                    name
                }
            }'
        );
        $response->assertJsonPath('data', null);
    }

    public function testIndividualPublicationsCanBeQueriedById()
    {
        $publication = Publication::factory()->create([
            'name' => 'Custom Publication for Unit Testing 1',
        ]);
        $response = $this->graphQL(
            'query GetPublication($id: ID!) {
                publication (id: $id) {
                    id
                    name
                }
            }',
            [ 'id' => $publication->id ]
        );
        $expected_data = [
            'publication' => [
                'id' => (string)$publication->id,
                'name' => 'Custom Publication for Unit Testing 1',
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    public function testPublicationThatDoesNotExistCanBeQueriedById()
    {
        $response = $this->graphQL(
            'query GetPublication {
                publication (id: "invalid") {
                    id
                    name
                }
            }'
        );
        $expected_data = [
            'publication' => null
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    public function testAllPublicationsCanBeQueried()
    {
        $publication_1 = Publication::factory()->create([
            'name' => 'Custom Publication for Unit Testing 1',
        ]);
        $publication_2 = Publication::factory()->create([
            'name' => 'Custom Publication for Unit Testing 2',
        ]);
        $response = $this->graphQL(
            'query GetPublications {
                publications {
                    data {
                        id
                        name
                    }
                }
            }'
        );
        $expected_data = [
            'publications' => [
                'data' => [
                    [
                        'id' => (string)$publication_1->id,
                        'name' => 'Custom Publication for Unit Testing 1',
                    ],
                    [
                        'id' => (string)$publication_2->id,
                        'name' => 'Custom Publication for Unit Testing 2',
                    ],
                ],
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }
}
