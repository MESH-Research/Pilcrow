<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Coverage for Submission::getSubmittedAt, which derives the submitted-at time
 * from the audit trail. Auditing does not fire for console-driven test runs
 * (audit.console = false), so the relevant audit rows are created directly.
 */
class SubmissionSubmittedAtTest extends TestCase
{
    use RefreshDatabase;

    /**
     * getSubmittedAt returns the timestamp of the audit recording the DRAFT to
     * INITIALLY_SUBMITTED transition.
     *
     * @return void
     */
    public function test_returns_timestamp_of_draft_to_submitted_audit(): void
    {
        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->create(['status' => Submission::DRAFT]);

        $submittedAt = now()->startOfSecond();
        $submission->audits()->create([
            'event' => 'updated',
            'old_values' => ['status' => Submission::DRAFT],
            'new_values' => ['status' => Submission::INITIALLY_SUBMITTED],
            'created_at' => $submittedAt,
        ]);

        $this->assertEquals(
            $submittedAt,
            $submission->refresh()->getSubmittedAt()
        );
    }

    /**
     * getSubmittedAt returns null when no submitting audit exists.
     *
     * @return void
     */
    public function test_returns_null_without_a_submitting_audit(): void
    {
        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->create(['status' => Submission::DRAFT]);

        $this->assertNull($submission->getSubmittedAt());
    }
}
