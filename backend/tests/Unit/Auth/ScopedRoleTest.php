<?php
declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Auth\ScopedAbility;
use App\Auth\ScopedRole;
use App\Models\Submission;
use App\Models\User;
use Tests\TestCase;

/**
 * Exercises the scoped role -> ability definitions directly, against
 * in-memory models — the payoff of modelling roles/abilities as code:
 * authorization logic is unit-testable in isolation from the pivots and
 * persistence (no role assignments, no database fixtures).
 */
class ScopedRoleTest extends TestCase
{
    private function draft(): Submission
    {
        $s = new Submission();
        $s->status = Submission::DRAFT;

        return $s;
    }

    private function submitted(): Submission
    {
        $s = new Submission();
        $s->status = Submission::INITIALLY_SUBMITTED;

        return $s;
    }

    public function testPivotRoleIdMapsToCaseAndExcludesAppAdmin(): void
    {
        $this->assertSame(ScopedRole::Editor, ScopedRole::tryFrom(3));
        $this->assertSame(ScopedRole::Submitter, ScopedRole::tryFrom(6));
        // application_admin (id 1) is a global Bouncer role, not a scoped role.
        $this->assertNull(ScopedRole::tryFrom(1));
        $this->assertNull(ScopedRole::tryFrom(99));
    }

    public function testReviewerHasViewAndUpdateButNotStatus(): void
    {
        $user = new User();
        $this->assertTrue(ScopedRole::Reviewer->allows(ScopedAbility::SubmissionView, $this->submitted(), $user));
        $this->assertTrue(ScopedRole::Reviewer->allows(ScopedAbility::SubmissionUpdate, $this->submitted(), $user));
        $this->assertFalse(ScopedRole::Reviewer->allows(ScopedAbility::SubmissionUpdateStatus, $this->submitted(), $user));
    }

    public function testReviewCoordinatorGetsStatusUnconditionally(): void
    {
        $user = new User();
        // Absolute grant: allowed regardless of draft state.
        $this->assertTrue(ScopedRole::ReviewCoordinator->allows(ScopedAbility::SubmissionUpdateStatus, $this->submitted(), $user));
        $this->assertTrue(ScopedRole::ReviewCoordinator->allows(ScopedAbility::SubmissionUpdateStatus, $this->draft(), $user));
    }

    public function testSubmitterStatusIsDraftOnly(): void
    {
        $user = new User();
        // Conditional grant: status only while DRAFT.
        $this->assertTrue(ScopedRole::Submitter->allows(ScopedAbility::SubmissionUpdateStatus, $this->draft(), $user));
        $this->assertFalse(ScopedRole::Submitter->allows(ScopedAbility::SubmissionUpdateStatus, $this->submitted(), $user));
        // Title edit is absolute for a submitter.
        $this->assertTrue(ScopedRole::Submitter->allows(ScopedAbility::SubmissionUpdateTitle, $this->submitted(), $user));
    }

    public function testPublicationAdminIsASupersetOfEditor(): void
    {
        $user = new User();
        $entity = $this->submitted();
        foreach (ScopedRole::Editor->grants() as $grant) {
            $this->assertTrue(
                ScopedRole::PublicationAdmin->allows($grant->ability, $entity, $user),
                "PublicationAdmin should inherit Editor's {$grant->ability->value}"
            );
        }
        // ...plus its own.
        $this->assertTrue(ScopedRole::PublicationAdmin->allows(ScopedAbility::PublicationUpdate, $entity, $user));
        $this->assertFalse(ScopedRole::Editor->allows(ScopedAbility::PublicationUpdate, $entity, $user));
    }

    public function testEditorInheritsReviewerSubmissionView(): void
    {
        $user = new User();
        $this->assertTrue(ScopedRole::Editor->allows(ScopedAbility::SubmissionView, $this->submitted(), $user));
    }
}
