<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class UserQueryTest extends ApiTestCase
{
    use RefreshDatabase;

    /**
     * @return array
     */
    public function profileMetadataProvider(): array
    {
        return [
            [
                [
                    'academic_profiles' => [
                        'orcid_id' => 'https://orcid.org/members/regular_user',
                        'humanities_commons' => 'https://hcommons.org/members/regularuser',
                    ],
                    'social_media' => [
                        'google' => 'regularuser',
                        'twitter' => 'regularuser',
                        'facebook' => 'regularuser',
                        'instagram' => 'regularuser',
                        'linkedin' => 'regularuser',
                    ],
                    'position_title' => 'Regular User',
                    'specialization' => 'Regular',
                    'affiliation' => 'Regular Users',
                    'interest_keywords' => [
                        'regular',
                        'user',
                    ],
                    'biography' => 'I am a regular user.',
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
    public function testThatUserDetailsCanBeQueried(array $profile_metadata): void
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
                    name
                    email
                    username
                    profile_metadata {
                        academic_profiles {
                            orcid_id
                            humanities_commons
                        }
                        social_media {
                            google
                            twitter
                            facebook
                            instagram
                            linkedin
                        }
                        position_title
                        specialization
                        affiliation
                        interest_keywords
                        biography
                        websites
                    }
                }
            }',
            [ 'id' => $user->id ]
        );
        $response->assertJson([
            'data' => [
                'user' => [
                    'name' => 'Regular User',
                    'email' => 'regularuser@gmail.com',
                    'username' => 'regularuser',
                    'profile_metadata' => $profile_metadata,
                ],
            ],
        ]);
    }

    /**
     * @return array
     */
    public function searchUserTermsProvider(): array
    {
        return [
            ['name','abcdef'],
            ['email','ghijkl@gmail.com'],
            ['username','mnopqr'],
            ['all', 'aaaaaaaaaaaaaa'],
            ['all', '<html>'],
            ['all', null],
            ['all', '12345'],
            ['all', 12345],
            ['all', ''],
        ];
    }

    /**
     * @dataProvider searchUserTermsProvider
     * @return void
     */
    public function testThatAUserCanBeSearchedBySearchTerms(string $property_name, mixed $search_term): void
    {
        User::factory()->count(20)->create();
        User::factory()->create([
            'name' => 'abcdef',
            'email' => 'ghijkl@gmail.com',
            'username' => 'mnopqr',
        ]);

        $response = $this->graphQL(
            'query SearchUsers ($search_term: String) {
                userSearch (term: $search_term) {
                    data {
                        name
                        email
                        username
                    }
                }
            }',
            [ 'search_term' => (string)$search_term ]
        );

        if ($property_name == 'all') {
            $response->assertJson([
                'data' => [
                    'userSearch' => [
                        'data' => [ ],
                    ],
                ],
            ]);
        } else {
            $response->assertJson([
                'data' => [
                    'userSearch' => [
                        'data' => [
                            [
                                'name' => 'abcdef',
                                'email' => 'ghijkl@gmail.com',
                                'username' => 'mnopqr',
                            ],
                        ],
                    ],
                ],
            ]);
        }
    }
}
