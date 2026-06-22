<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Auth\ScopedRole;
use App\Models\Publication;
use App\Models\Submission;
use App\Models\SubmissionAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Coverage for the SubmissionAssignment pivot model's relations and the
 * orderBySubmission no-op branch on its builder.
 */
class SubmissionAssignmentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The pivot resolves the user, submission, and role it links together.
     *
     * @return void
     */
    public function test_relations_resolve_user_submission_and_role(): void
    {
        $user = User::factory()->create();
        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->hasAttached($user, [], 'reviewers')
            ->create();

        $assignment = SubmissionAssignment::query()->firstOrFail();

        $this->assertTrue($assignment->user->is($user));
        $this->assertTrue($assignment->submission->is($submission));
        $this->assertEquals(
            (int)ScopedRole::Reviewer->pivotValue(),
            (int)$assignment->role_id
        );
    }

    /**
     * orderBySubmission is a no-op when given a null clause set, returning the
     * unmodified assignment set rather than throwing.
     *
     * @return void
     */
    public function test_order_by_submission_is_noop_when_null(): void
    {
        $user = User::factory()->create();
        Submission::factory()
            ->for(Publication::factory()->create())
            ->hasAttached($user, [], 'reviewers')
            ->create();

        $results = SubmissionAssignment::query()->orderBySubmission(null)->get();

        $this->assertCount(1, $results);
    }
}
