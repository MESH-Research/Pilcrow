<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\ApiTestCase;

class UserPermissionsTest extends ApiTestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    /**
     * A user's assigned global role is queryable and exposes its title as
     * `name` (the client contract preserved across the move to Bouncer).
     *
     * @return void
     */
    public function testRoleForUserIsQueryableFromGraphqlEndpoint()
    {
        $this->beAppAdmin();
        $user = User::factory()->create();
        $user->assignRole(Role::APPLICATION_ADMINISTRATOR);

        $adminRole = Role::where('name', Role::SLUG_APPLICATION_ADMIN)->firstOrFail();

        $response = $this->graphQL(
            'query getUser($id: ID) {
                user(id: $id) {
                    id
                    name
                    roles {
                        id
                        name
                    }
                }
            }',
            ['id' => $user->id]
        );

        $response->assertJsonPath('data.user.roles', [
            [
                'id' => (string)$adminRole->id,
                'name' => Role::APPLICATION_ADMINISTRATOR,
            ],
        ]);
    }

    /**
     * A user with no assigned global role returns an empty roles array.
     * (Publication/submission roles live in pivots, not the global roles.)
     *
     * @return void
     */
    public function testUserWithNoRoleReturnsAnEmptyArrayWhenRolesForUserAreQueriedFromGraphqlEndpoint()
    {
        $this->beAppAdmin();
        $user = User::factory()->create();

        $response = $this->graphQL(
            'query getUser($id: ID) {
                user(id: $id) {
                    id
                    name
                    roles {
                        id
                        name
                    }
                }
            }',
            ['id' => $user->id]
        );

        $response->assertJsonPath('data.user.roles', []);
    }
}
