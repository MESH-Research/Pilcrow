<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\Publication;
use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class PublicationUserSearchTest extends ApiTestCase
{
    use RefreshDatabase;

    private function querySearch(string|int $publicationId, ?string $term = null): \Illuminate\Testing\TestResponse
    {
        return $this->graphQL(
            'query PubUserSearch ($id: ID, $term: String) {
                publication (id: $id) {
                    id
                    user_search (term: $term) { id username }
                }
            }',
            ['id' => (string)$publicationId, 'term' => $term]
        );
    }

    public function testGuestCannotSearchUsersForAPublication(): void
    {
        $publication = Publication::factory()->create();
        $response = $this->querySearch($publication->id);

        // The publication query is itself protected; user_search should not
        // be reachable. Either the publication resolves to null due to the
        // existing @can on publication, or user_search errors. Either way
        // the field must not return data.
        $this->assertNull($response->json('data.publication.user_search'));
    }

    public function testUnaffiliatedAuthedUserCannotSearchUsersForAPublication(): void
    {
        $publication = Publication::factory()->create(['is_publicly_visible' => true]);
        $outsider = User::factory()->create();
        $this->actingAs($outsider);

        $response = $this->querySearch($publication->id);

        $this->assertNull($response->json('data.publication.user_search'));
        $this->assertNotEmpty($response->json('errors'));
    }

    public function testApplicationAdministratorCanSearchAnyPublication(): void
    {
        $admin = $this->beAppAdmin();

        $publication = Publication::factory()->create();
        $member = User::factory()->create(['username' => 'pubmember']);
        $publication->editors()->save($member);

        $response = $this->querySearch($publication->id);

        $usernames = collect($response->json('data.publication.user_search'))
            ->pluck('username');
        $this->assertContains('pubmember', $usernames);
    }

    public function testEditorCanSearchUsersInOwnPublication(): void
    {
        $publication = Publication::factory()->create();
        $editor = User::factory()->create();
        $publication->editors()->save($editor);
        $this->actingAs($editor);

        $other = User::factory()->create(['username' => 'colleague']);
        $publication->editors()->save($other);

        $response = $this->querySearch($publication->id);

        $usernames = collect($response->json('data.publication.user_search'))
            ->pluck('username');
        $this->assertContains('colleague', $usernames);
    }

    public function testSubmitterCanSearchUsersInTheirSubmissionsPublication(): void
    {
        $publication = Publication::factory()->create();
        $submitter = User::factory()->create();
        $reviewer = User::factory()->create(['username' => 'thereviewer']);

        Submission::factory()
            ->for($publication)
            ->hasAttached($submitter, [], 'submitters')
            ->hasAttached($reviewer, [], 'reviewers')
            ->create();

        $this->actingAs($submitter);

        $response = $this->querySearch($publication->id);

        $usernames = collect($response->json('data.publication.user_search'))
            ->pluck('username');
        $this->assertContains('thereviewer', $usernames);
    }

    public function testSearchResultsAreScopedToPublicationRelations(): void
    {
        $publication = Publication::factory()->create();
        $editor = User::factory()->create();
        $publication->editors()->save($editor);
        $this->actingAs($editor);

        // Unrelated user not associated with the publication or its submissions
        User::factory()->create(['username' => 'stranger']);

        $response = $this->querySearch($publication->id);

        $usernames = collect($response->json('data.publication.user_search'))
            ->pluck('username');
        $this->assertNotContains('stranger', $usernames);
    }
}
