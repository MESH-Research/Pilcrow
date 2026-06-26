<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Auth\Abilities\SubmissionAbility;
use App\Auth\ScopedAbilityResolver;
use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The abilities resolver reads eager-loaded pivot relations instead of querying
 * per entity, so list endpoints that select `abilities` don't N+1. This pins
 * both halves: the loaded path issues no pivot query, and it returns the same
 * verdict as the unloaded (query) path.
 */
class ScopedAbilityEagerLoadTest extends TestCase
{
    use RefreshDatabase;

    private function pivotQueryCount(callable $callback): int
    {
        DB::enableQueryLog();
        DB::flushQueryLog();
        $callback();
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        return count(array_filter($queries, static function (array $query): bool {
            return str_contains($query['query'], 'submission_user')
                || str_contains($query['query'], 'publication_user');
        }));
    }

    public function testLoadedRelationsAvoidPivotQueries(): void
    {
        $editor = User::factory()->create();
        $publication = Publication::factory()
            ->hasAttached($editor, [], 'editors')
            ->create();
        $submission = Submission::factory()->for($publication)->create();

        // Eager-load exactly what the @with directives load on the list endpoint.
        $submission->load(['submissionAssignments', 'publication.publicationAssignments']);

        $resolver = new ScopedAbilityResolver();

        $count = $this->pivotQueryCount(function () use ($resolver, $editor, $submission) {
            $resolver->allows($editor, SubmissionAbility::View, $submission);
        });

        $this->assertSame(0, $count, 'Resolver should not query pivots when relations are loaded.');
        // The editor inherits submission view through the parent publication.
        $this->assertTrue($resolver->allows($editor, SubmissionAbility::View, $submission));
    }

    public function testLoadedAndUnloadedPathsAgree(): void
    {
        $editor = User::factory()->create();
        $reviewer = User::factory()->create();
        $publication = Publication::factory()
            ->hasAttached($editor, [], 'editors')
            ->create();
        $submission = Submission::factory()
            ->for($publication)
            ->hasAttached($reviewer, [], 'reviewers')
            ->create();

        foreach ([$editor, $reviewer] as $user) {
            $unloaded = (new ScopedAbilityResolver())
                ->allows($user, SubmissionAbility::Invite, $submission->fresh());

            $loaded = Submission::query()
                ->with(['submissionAssignments', 'publication.publicationAssignments'])
                ->find($submission->id);
            $fromLoaded = (new ScopedAbilityResolver())
                ->allows($user, SubmissionAbility::Invite, $loaded);

            $this->assertSame($unloaded, $fromLoaded);
        }
    }
}
