<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Auth\ScopedRole;
use App\Models\Publication;
use App\Models\PublicationAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Coverage for the PublicationAssignment pivot model's relations, which back
 * the admin publication-membership views.
 */
class PublicationAssignmentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The pivot resolves the user, publication, and role it links together.
     *
     * @return void
     */
    public function test_relations_resolve_user_publication_and_role(): void
    {
        $user = User::factory()->create();
        $publication = Publication::factory()->create();
        $publication->editors()->save($user);

        $assignment = PublicationAssignment::query()->firstOrFail();

        $this->assertTrue($assignment->user->is($user));
        $this->assertTrue($assignment->publication->is($publication));
        $this->assertEquals(
            (int)ScopedRole::Editor->pivotValue(),
            (int)$assignment->role_id
        );
    }
}
