<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;
use App\Models\User;

class UserQueryTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    /**
     * @return array
     */
    public function profileMetadataProvider()
    {
        return [
            [
                [
                    "salutation" => "Mrs.",
                    "phone" => "(123) 456-7890",
                    "mailing_address" => [
                        "city" => "Seattle",
                        "state" => "WA",
                        "postal_code" => "98052",
                        "street_address" => "20341 Whitworth Institute 405 N. Whitworth Dr."
                    ],
                    "orchid_id" => "https://orcid.org/members/regular_user",
                    "humanities_commons" => "https://hcommons.org/members/regularuser",
                    "social_media" => [
                        "google" => "regularuser",
                        "twitter" => "regularuser",
                        "facebook" => "regularuser",
                        "instagram" => "regularuser",
                        "linkedin" => "regularuser",
                        "academia.edu_id" => "regularuser",
                        "skype" => "regularuser",
                        "zoom" => "regularuser",
                    ],
                    "professional_title" => "Regular User",
                    "specialization" => "Regular",
                    "affiliation" => "Regular Users",
                    "interest_keywords" => [
                        "regular",
                        "user"
                    ],
                    "disinterest_keywords" => [
                        "nonregular"
                    ],
                    "biography" => "I am a regular user.",
                    "profile_picture" => "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=",
                    "websites" => [
                        "https://github.com"
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider profileMetadataProvider
     * @return void
     */
    public function testThatUserDetailsCanBeQueried(?array $data): void
    {
        $profile_metadata = json_encode($data);
        $user = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'regularuser@gmail.com',
            'username' => 'regularuser',
            'profile_metadata' => $profile_metadata,
        ]);
        // This saves JSON as double-escaped
        $response = $this->graphQL(
            'query getUser ($id: ID!) {
                user (id: $id) {
                    name
                    email
                    username
                    profile_metadata
                }
            }',
            [ 'id' => $user->id ]
        );
        $response->assertJsonPath('data.user.name', 'Regular User');
        $response->assertJsonPath('data.user.email', 'regularuser@gmail.com');
        $response->assertJsonPath('data.user.username', 'regularuser');
        $response->assertJsonPath('data.user.profile_metadata', json_encode($profile_metadata));
    }
}
