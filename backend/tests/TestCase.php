<?php
declare(strict_types=1);

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Act as a new user with application administrator role
     *
     * @return App\Models\User New user with adminsitrator role
     */
    public function beAppAdmin(): User
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->actingAs($user);

        return $user;
    }

    /**
     * Remove the trace key from error arrays.
     *
     * Useful for debugging as the trace key overwhelms var_dump ouput of a response json.
     *
     * @param array $json
     * @return array
     */
    public function unsetJsonTrace($json): array
    {
        if (empty($json['errors'])) {
            return $json;
        }

        foreach ($json['errors'] as $index => $error) {
            unset($json['errors'][$index]['trace']);
        }

        return $json;
    }
}
