<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Record of Review derives its "review completed" date from an audit
 * entry whose new_values.status is a post-review state. That audit is only
 * written when a real status-changing save fires the OwenIt auditing
 * observer, which is disabled for console runs by default (audit.console).
 *
 * These tests pin that load-bearing behaviour: a status change made while
 * console auditing is enabled produces the audit row the feature reads.
 */
class SubmissionStatusAuditTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A status-changing save records an audit whose new_values.status holds
     * the new (post-review) status.
     *
     * @return void
     */
    public function test_status_change_records_audit_with_new_status(): void
    {
        config(['audit.console' => true]);

        // CreatedUpdatedBy reads auth()->user()->id on create/update.
        $this->actingAs(User::factory()->create());

        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->create(['status' => Submission::DRAFT]);

        $submission->status = Submission::ACCEPTED_AS_FINAL;
        $submission->save();

        $statuses = $submission->audits
            ->pluck('new_values')
            ->map(fn($values) => $values['status'] ?? null)
            ->filter(fn($status) => $status !== null);

        $this->assertTrue(
            $statuses->contains(Submission::ACCEPTED_AS_FINAL),
            'Expected an audit recording the transition into ACCEPTED_AS_FINAL.'
        );
    }

    /**
     * Sanity check on the guard: with console auditing disabled (the default),
     * the seeder/test reliance on enabling it is justified because no audit is
     * written for a console-driven status change.
     *
     * @return void
     */
    public function test_status_change_writes_no_audit_when_console_disabled(): void
    {
        config(['audit.console' => false]);

        $this->actingAs(User::factory()->create());

        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->create(['status' => Submission::DRAFT]);

        $submission->status = Submission::ACCEPTED_AS_FINAL;
        $submission->save();

        $this->assertCount(0, $submission->audits);
    }
}
