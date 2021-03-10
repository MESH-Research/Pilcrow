<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

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
                    'salutation' => 'Mrs.',
                    'phone' => '(123) 456-7890',
                    'mailing_address' => [
                        'city' => 'Seattle',
                        'state' => 'WA',
                        'postal_code' => '98052',
                        'street_address' => '20341 Whitworth Institute 405 N. Whitworth Dr.',
                    ],
                    'orchid_id' => 'https://orcid.org/members/regular_user',
                    'humanities_commons' => 'https://hcommons.org/members/regularuser',
                    'social_media' => [
                        'google' => 'regularuser',
                        'twitter' => 'regularuser',
                        'facebook' => 'regularuser',
                        'instagram' => 'regularuser',
                        'linkedin' => 'regularuser',
                        'academia_edu_id' => 'regularuser',
                        'skype' => 'regularuser',
                        'zoom' => 'regularuser',
                    ],
                    'professional_title' => 'Regular User',
                    'specialization' => 'Regular',
                    'affiliation' => 'Regular Users',
                    'interest_keywords' => [
                        'regular',
                        'user',
                    ],
                    'disinterest_keywords' => [
                        'nonregular',
                        'irregular',
                    ],
                    'biography' => 'I am a regular user.',
                    'profile_picture' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
                    'websites' => [
                        'https://github.com',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider profileMetadataProvider
     * @return void
     */
    public function testThatUserDetailsCanBeQueried(?array $profile_metadata): void
    {
        $user = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'regularuser@gmail.com',
            'username' => 'regularuser',
            'profile_metadata' => $profile_metadata,
        ]);
        $response = $this->graphQL(
            'query getUser ($id: ID!) {
                user (id: $id) {
                    profile_metadata {
                        salutation
                        phone
                        mailing_address {
                            city
                            state
                            postal_code
                            street_address
                        }
                        orchid_id
                        humanities_commons
                        social_media {
                            google
                            twitter
                            facebook
                            instagram
                            linkedin
                            academia_edu_id
                            skype
                            zoom
                        }
                        professional_title
                        specialization
                        affiliation
                        interest_keywords
                        disinterest_keywords
                        biography
                        profile_picture
                        websites
                    }
                }
            }',
            [ 'id' => $user->id ]
        );
        $response->assertJson([
            'data' => [
                'user' => [
                    'profile_metadata' => $profile_metadata,
                ],
            ],
        ]);
    }
}
