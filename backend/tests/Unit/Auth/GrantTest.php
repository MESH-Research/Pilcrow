<?php
declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Auth\Abilities\SubmissionAbility;
use App\Auth\Grants\Grant;
use App\Auth\Grants\Predicates\SubmissionIsDraft;
use App\Models\Submission;
use App\Models\User;
use Tests\TestCase;

/**
 * Exercises a single Grant in isolation — the unit that pairs an ability with an
 * optional predicate. No pivots, no database: pure code.
 */
class GrantTest extends TestCase
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

    public function testGrantDoesNotPermitADifferentAbility(): void
    {
        $grant = new Grant(SubmissionAbility::View);

        $this->assertFalse(
            $grant->permits(SubmissionAbility::UpdateContent, $this->draft(), new User())
        );
    }

    public function testAbsoluteGrantPermitsItsAbilityRegardlessOfEntity(): void
    {
        $grant = new Grant(SubmissionAbility::View);

        $this->assertTrue($grant->permits(SubmissionAbility::View, $this->draft(), new User()));
        $this->assertTrue($grant->permits(SubmissionAbility::View, null, new User()));
    }

    public function testConditionalGrantHonorsItsPredicate(): void
    {
        $grant = new Grant(SubmissionAbility::UpdateStatus, new SubmissionIsDraft());

        $this->assertTrue(
            $grant->permits(SubmissionAbility::UpdateStatus, $this->draft(), new User())
        );
        $this->assertFalse(
            $grant->permits(SubmissionAbility::UpdateStatus, $this->submitted(), new User())
        );
    }

    public function testConditionalGrantIsDeniedWhenNoEntityIsSupplied(): void
    {
        $grant = new Grant(SubmissionAbility::UpdateStatus, new SubmissionIsDraft());

        $this->assertFalse(
            $grant->permits(SubmissionAbility::UpdateStatus, null, new User())
        );
    }
}
