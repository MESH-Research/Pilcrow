<?php

namespace Tests\Api;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\ApiTestCase;


class PublicationScopesTest extends ApiTestCase
{

    use RefreshDatabase;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $PublicationFactory = Publication::factory();

        $PublicationFactory
            ->state(new Sequence(
                ['is_publically_visible' => true,],
                ['is_publically_visible' => false]
            ))
            ->count(2)
            ->create();

        $PublicationFactory
            ->hasAttached($this->user, [], 'editors')
            ->create();
        $PublicationFactory
            ->hasAttached($this->user, [], 'publicationAdmins')
            ->create();
    }

    public static function publicationScopeDataProvider(): array
    {
        return [
            'public' => [
                'public' => true,
                'expectedCount' => 3
            ]
        ];
    }

    #[DataProvider('publicationScopeDataProvider')]
    public function testPublicationScope(bool $public, int $expectedCount)
    {
        $query =
            /** @lang GraphQL */
            '
            query GetPublicationsScope($public: Boolean) {
                publications (page: 1, first: 100, public: $public) {
                    paginatorInfo {
                        count
                    }
                    data {
                        is_publicly_visible
                    }
                }
            }
        ';

        $response = $this->graphQL($query, ['public' => $public]);

        $response->assertJsonPath('data.publications.paginatorInfo.count', $expectedCount);
    }

    public function testNothing()
    {
        $this->assertTrue(true);
    }
}
