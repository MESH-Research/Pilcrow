<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class UserQueryTest extends ApiTestCase
{
    use RefreshDatabase;

    /**
     * @return array
     */
    public static function profileMetadataProvider(): array
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
                    'biography' => 'I am a regular user.',
                    'websites' => [
                        'https://github.com',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return void
     */
    #[DataProvider('profileMetadataProvider')]
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
                        biography
                        websites
                    }
                }
            }',
            ['id' => $user->id]
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
    public static function searchUserTermsProvider(): array
    {
        return [
            [
                'searchTerm' => 'abcdef',
                'shouldFind' => "ghijkl@gmail.com",
                'count' => 1,
            ],
            [
                'searchTerm' => 'ghijkl@gmail.com',
                'shouldFind' => "ghijkl@gmail.com",
                'count' => 1,
            ],
            [
                'searchTerm' => 'mnopqr',
                'shouldFind' => "ghijkl@gmail.com",
                'count' => 1
            ],
            [
                'searchTerm' => 'aaaaaaaaaaaaaa',
                'shouldFind' => null,
                'count' => 0
            ],
            [
                'searchTerm' => '<html>',
                'shouldFind' => null,
                'count' => 0
            ],
            [
                'searchTerm' => null,
                'shouldFind' => null,
                'count' => 10
            ],
            [
                'searchTerm' => '12345',
                'shouldFind' => null,
                'count' => 0
            ],
            [
                'searchTerm' => 12345,
                'shouldFind' => null,
                'count' => 0
            ],
            [
                'searchTerm' => '',
                'shouldFind' => null,
                'count' => 10
            ],
        ];
    }

    /**
     * @param mixed $searchTerm
     * @param string|null $shouldFind
     * @param int $count
     * @return void
     */
    #[DataProvider('searchUserTermsProvider')]
    public function testSearchingForUsers(mixed $searchTerm = null, ?string $shouldFind = null, int $count = 0): void
    {
        User::factory()->createManyQuietly(20);
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
            ['search_term' => (string)$searchTerm]
        );

        $data = $response->json('data.userSearch.data');
        $collection = collect($data);

        if ($shouldFind !== null) {
            $this->assertTrue(
                $collection->contains('email', $shouldFind),
                "Search term '{$searchTerm}' should return user with email '{$shouldFind}', but returned: " . $collection->implode('email', ', ')
            );
        }
        $this->assertCount(
            $count,
            $data,
            "Search term '{$searchTerm}' should return {$count} results, but returned " . count($data)
        );
    }
}
