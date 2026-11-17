<?php
declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Auth\Abilities\PublicationAbility;
use App\Auth\Abilities\SubmissionAbility;
use App\Auth\Roles\GlobalRole;
use App\Auth\Roles\ScopedRole;
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

    private function reviewable(): Submission
    {
        $s = new Submission();
        $s->status = Submission::UNDER_REVIEW;

        return $s;
    }

    public function testPivotRoleIdMapsToCaseAndExcludesAppAdmin(): void
    {
        $this->assertSame(ScopedRole::Editor, ScopedRole::tryFrom('editor'));
        $this->assertSame(ScopedRole::Submitter, ScopedRole::tryFrom('submitter'));
        // application_admin is a global Bouncer role, not a scoped role.
        $this->assertNull(ScopedRole::tryFrom('application_admin'));
        $this->assertNull(ScopedRole::tryFrom('not-a-role'));
    }

    public function testReviewerReviewsWhileReviewableButHoldsNoContentUpdate(): void
    {
        $user = new User();
        // Transitional: the reviewer keeps View; reviews only while reviewable.
        $this->assertTrue(ScopedRole::Reviewer->allows(SubmissionAbility::View, $this->submitted(), $user));
        $this->assertTrue(ScopedRole::Reviewer->allows(SubmissionAbility::Review, $this->reviewable(), $user));
        $this->assertFalse(ScopedRole::Reviewer->allows(SubmissionAbility::Review, $this->submitted(), $user));
        // The manuscript-edit hole is closed: a reviewer holds no content Update,
        // and no status control.
        $this->assertFalse(ScopedRole::Reviewer->allows(SubmissionAbility::UpdateContent, $this->draft(), $user));
        $this->assertFalse(ScopedRole::Reviewer->allows(SubmissionAbility::UpdateStatus, $this->submitted(), $user));
    }

    public function testSubmitterOwnsContentAndSubmitWhileDraftOnly(): void
    {
        $user = new User();
        $this->assertTrue(ScopedRole::Submitter->allows(SubmissionAbility::UpdateContent, $this->draft(), $user));
        $this->assertFalse(ScopedRole::Submitter->allows(SubmissionAbility::UpdateContent, $this->submitted(), $user));
        $this->assertTrue(ScopedRole::Submitter->allows(SubmissionAbility::Submit, $this->draft(), $user));
        $this->assertFalse(ScopedRole::Submitter->allows(SubmissionAbility::Submit, $this->submitted(), $user));
    }

    public function testReviewCoordinatorGetsStatusUnconditionally(): void
    {
        $user = new User();
        // Absolute grant: allowed regardless of draft state.
        $this->assertTrue(ScopedRole::ReviewCoordinator->allows(SubmissionAbility::UpdateStatus, $this->submitted(), $user));
        $this->assertTrue(ScopedRole::ReviewCoordinator->allows(SubmissionAbility::UpdateStatus, $this->draft(), $user));
    }

    public function testSubmitterStatusIsDraftOnly(): void
    {
        $user = new User();
        // Conditional grant: status only while DRAFT.
        $this->assertTrue(ScopedRole::Submitter->allows(SubmissionAbility::UpdateStatus, $this->draft(), $user));
        $this->assertFalse(ScopedRole::Submitter->allows(SubmissionAbility::UpdateStatus, $this->submitted(), $user));
    }

    public function testPublicationAdminIsASupersetOfEditor(): void
    {
        // Inheritance is structural: PublicationAdmin's granted abilities are a
        // superset of Editor's. Asserted on the ability SET rather than by
        // evaluating allows() against a fixed entity, because some grants are
        // comment-scoped (their predicate needs a Comment, not a submission) and
        // would spuriously fail a submission-entity probe. Predicate evaluation
        // is covered per-grant by the tests above.
        $editorAbilities = array_map(fn($grant) => $grant->ability, ScopedRole::Editor->grants());
        $adminAbilities = array_map(fn($grant) => $grant->ability, ScopedRole::PublicationAdmin->grants());

        foreach ($editorAbilities as $ability) {
            $this->assertContains(
                $ability,
                $adminAbilities,
                "PublicationAdmin should inherit Editor's {$ability->value}"
            );
        }

        // ...plus its own: the absolute publication update Editor lacks.
        $this->assertContains(PublicationAbility::Update, $adminAbilities);
        $this->assertNotContains(PublicationAbility::Update, $editorAbilities);
    }

    public function testEditorInheritsReviewerSubmissionView(): void
    {
        $user = new User();
        $this->assertTrue(ScopedRole::Editor->allows(SubmissionAbility::View, $this->submitted(), $user));
    }

    /**
     * legacyId() is frozen, not cosmetic: the role relations and invite
     * mutations dual-write it into the retained pivot `role_id` so a rollback to
     * the pre-slug code reads valid data. Changing a value here would silently
     * corrupt that recovery net on every new row until `role_id` is dropped.
     * (Also the `highest_privileged_role` rank.)
     */
    public function testLegacyIdMappingIsFrozen(): void
    {
        $this->assertSame(2, ScopedRole::PublicationAdmin->legacyId());
        $this->assertSame(3, ScopedRole::Editor->legacyId());
        $this->assertSame(4, ScopedRole::ReviewCoordinator->legacyId());
        $this->assertSame(5, ScopedRole::Reviewer->legacyId());
        $this->assertSame(6, ScopedRole::Submitter->legacyId());

        // application_admin is the global role; its id 1 lives on GlobalRole.
        $this->assertSame(1, GlobalRole::ApplicationAdministrator->legacyId());
    }
}
