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

    private const PUBLICATION_ADMINISTRATOR_ID = 2;

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
                'Test Publication',
                [
                    'createPublication' => [
                        'name' => 'Test Publication',
                    ],
                ],
            ],
            [
                '        Test Publication with Whitespace       ',
                [
                    'createPublication' => [
                        'name' => 'Test Publication with Whitespace',
                    ],
                ],
            ],
            [
                '',
                null,
            ],
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
            }',
            [ 'publication_name' => $publication_name ]
        );
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testPublicationsCannotBeCreatedViaMutationByARegularUser()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->graphQL(
            'mutation CreatePublication {
                createPublication(publication:{name:"Test Publication Created by Regular User"}) {
                    id
                    name
                }
            }'
        );
        $response->assertJsonPath('data', null);
    }

    /**
     * @return void
     */
    public function testIndividualPublicationsCanBeQueriedById()
    {
        $publication = Publication::factory()->create([
            'name' => 'Test Publication for Querying an Individual Publication',
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
                'name' => 'Test Publication for Querying an Individual Publication',
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testPublicationThatDoesNotExistCanBeQueriedById()
    {
        $response = $this->graphQL(
            'query GetPublication {
                publication (id: "Invalid ID") {
                    id
                    name
                }
            }'
        );
        $expected_data = [
            'publication' => null,
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testAllPublicationsCanBeQueriedByAnApplicationAdministrator()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->actingAs($user);

        $publication_1 = Publication::factory()->create([
            'name' => 'Test Publication 1 for Querying All Publications',
        ]);
        $publication_2 = Publication::factory()->create([
            'name' => 'Test Publication 2 for Querying All Publications',
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
                        'name' => 'Test Publication 1 for Querying All Publications',
                    ],
                    [
                        'id' => (string)$publication_2->id,
                        'name' => 'Test Publication 2 for Querying All Publications',
                    ],
                ],
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testAllPublicationsCannotBeQueriedByARegularUser()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

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
            'publications' => null,
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testPublicationsHaveAManyToManyRelationshipWithUsers()
    {
        $publication_count = 4;
        $user_count = 6;
        $users = User::factory()->count($user_count)->create();

        // Create publications and attach them to users randomly with random roles
        for ($i = 0; $i < $publication_count; $i++) {
            $random_role_id = Role::whereIn(
                'name',
                [
                    Role::PUBLICATION_ADMINISTRATOR,
                    Role::EDITOR,
                ]
            )
                ->get()
                ->pluck('id')
                ->random();
            $publication = Publication::factory()->hasAttached(
                $users->random(),
                [
                    'role_id' => $random_role_id,
                ]
            )
                ->create();
            // Ensure at least one publication admin is attached to the publication.
            if ($random_role_id !== self::PUBLICATION_ADMINISTRATOR_ID) {
                $publication->users()->attach(
                    $users->random(),
                    [
                        'role_id' => self::PUBLICATION_ADMINISTRATOR_ID,
                    ]
                );
            }
        }
        Publication::all()->map(function ($publication) {
            $this->assertGreaterThan(0, $publication->users->count());
            $this->assertLessThanOrEqual(2, $publication->users->count());
            $publication->users->map(function ($user) {
                $this->assertIsInt(
                    User::where(
                        'id',
                        $user->id
                    )->firstOrFail()->id
                );
            });
        });
        User::all()->map(function ($user) use ($publication_count) {
            $this->assertLessThanOrEqual($publication_count, $user->publications->count());
            $user->publications->map(function ($publication) {
                $this->assertIsInt(
                    Publication::where(
                        'id',
                        $publication->id
                    )->firstOrFail()->id
                );
            });
        });
    }

    /**
     * @return void
     */
    public function testUserRoleAndUserAreUniqueForAPublication()
    {
        $user = User::factory()->create();
        $role_id = Role::where('name', Role::PUBLICATION_ADMINISTRATOR)->first()->id;

        $publication = Publication::factory()->hasAttached(
            $user,
            [
                'role_id' => $role_id,
            ]
        )
            ->create();
        $this->expectException(QueryException::class);
        $publication->users()->attach(
            $user,
            [
                'role_id' => $role_id,
            ]
        );
        $publication_pivot_data = PublicationUser::where(
            [
                'user_id' => $user->id,
                'role_id' => $role_id,
                'publication_id' => $publication->id,
            ]
        )
            ->get();
        $this->assertEquals(1, $publication_pivot_data->count());
    }
}
