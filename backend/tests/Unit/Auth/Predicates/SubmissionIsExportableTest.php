<?php
declare(strict_types=1);

namespace Tests\Unit\Auth\Predicates;

use App\Auth\Grants\Predicates\SubmissionIsExportable;
use App\Models\Submission;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Locks the exportable-status set the predicate enforces. This is the condition
 * the client used to hard-code; pinning it here keeps the server the single
 * source of truth and fails loudly if the set drifts.
 */
class SubmissionIsExportableTest extends TestCase
{
    /**
     * @return array<string, array{0: int, 1: bool}>
     */
    public static function statusProvider(): array
    {
        return [
            'DRAFT' => [Submission::DRAFT, false],
            'INITIALLY_SUBMITTED' => [Submission::INITIALLY_SUBMITTED, false],
            'RESUBMISSION_REQUESTED' => [Submission::RESUBMISSION_REQUESTED, true],
            'RESUBMITTED' => [Submission::RESUBMITTED, false],
            'REJECTED' => [Submission::REJECTED, true],
            'ACCEPTED_AS_FINAL' => [Submission::ACCEPTED_AS_FINAL, true],
            'EXPIRED' => [Submission::EXPIRED, true],
            'UNDER_REVIEW' => [Submission::UNDER_REVIEW, false],
            'ARCHIVED' => [Submission::ARCHIVED, true],
            'DELETED' => [Submission::DELETED, false],
        ];
    }

    /**
     * @param int $status
     * @param bool $expected
     * @return void
     */
    #[DataProvider('statusProvider')]
    public function testHoldsOnlyForExportableStatuses(int $status, bool $expected): void
    {
        $submission = new Submission();
        $submission->status = $status;

        $this->assertSame(
            $expected,
            (new SubmissionIsExportable())->holds($submission, new User())
        );
    }
}
