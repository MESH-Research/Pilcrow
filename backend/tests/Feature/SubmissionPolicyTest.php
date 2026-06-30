<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Auth\Roles\GlobalRole;
use App\Auth\Roles\ScopedRole;
use App\Models\InlineComment;
use App\Models\OverallComment;
use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Characterization tests for SubmissionPolicy.
 *
 * These lock in the CURRENT (Spatie + custom pivot) behavior so the planned
 * RBAC -> ABAC / Bouncer migration cannot silently change authorization.
 * They assert what the code does today, including its quirks (e.g. submitters
 * can only update status while DRAFT, `create` ignores the caller's role,
 * `invite` is granted to review coordinators and up via the ScopedRole matrix).
 */
class SubmissionPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function appAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(GlobalRole::ApplicationAdministrator);

        return $admin;
    }

    private function attachToPublication(User $user, Publication $publication, string $roleId): void
    {
        $user->publications()->attach($publication->id, ['role' => $roleId]);
    }

    private function attachToSubmission(User $user, Submission $submission, string $roleId): void
    {
        $submission->users()->attach($user->id, ['role' => $roleId]);
    }

    /**
     * Submission with its own publication, default status DRAFT.
     */
    private function makeSubmission(int $status = Submission::DRAFT): Submission
    {
        $publication = Publication::factory()->create();

        return Submission::factory()->for($publication)->create(['status' => $status]);
    }

    // ---- create (role-agnostic: only the publication's accepting flag) ------

    public function testCreateAllowsAnyUserWhenPublicationAcceptingSubmissions(): void
    {
        $publication = Publication::factory()->create(['is_accepting_submissions' => true]);

        $this->assertTrue(
            User::factory()->create()->can('create', [Submission::class, ['publication_id' => $publication->id]])
        );
    }

    public function testCreateDeniedWhenPublicationNotAcceptingSubmissions(): void
    {
        $publication = Publication::factory()->create(['is_accepting_submissions' => false]);

        $this->assertFalse(
            User::factory()->create()->can('create', [Submission::class, ['publication_id' => $publication->id]])
        );
    }

    // ---- updateSubmitters ---------------------------------------------------

    public function testUpdateSubmittersAllowsApplicationAdministrator(): void
    {
        $this->assertTrue($this->appAdmin()->can('updateSubmitters', $this->makeSubmission()));
    }

    public function testUpdateSubmittersAllowsPublicationAdminAndEditor(): void
    {
        foreach ([ScopedRole::PublicationAdmin->toSlug(), ScopedRole::Editor->toSlug()] as $roleId) {
            $submission = $this->makeSubmission();
            $user = User::factory()->create();
            $this->attachToPublication($user, $submission->publication, $roleId);

            $this->assertTrue($user->can('updateSubmitters', $submission), "role_id $roleId");
        }
    }

    public function testUpdateSubmittersAllowsSubmitterAndReviewCoordinator(): void
    {
        foreach ([ScopedRole::Submitter->toSlug(), ScopedRole::ReviewCoordinator->toSlug()] as $roleId) {
            $submission = $this->makeSubmission();
            $user = User::factory()->create();
            $this->attachToSubmission($user, $submission, $roleId);

            $this->assertTrue($user->can('updateSubmitters', $submission), "role_id $roleId");
        }
    }

    public function testUpdateSubmittersDeniesReviewer(): void
    {
        $submission = $this->makeSubmission();
        $reviewer = User::factory()->create();
        $this->attachToSubmission($reviewer, $submission, ScopedRole::Reviewer->toSlug());

        $this->assertFalse($reviewer->can('updateSubmitters', $submission));
    }

    public function testUpdateSubmittersDeniesUnaffiliatedUser(): void
    {
        $this->assertFalse(User::factory()->create()->can('updateSubmitters', $this->makeSubmission()));
    }

    // ---- updateReviewers ----------------------------------------------------

    public function testUpdateReviewersAllowsAdminAndReviewCoordinator(): void
    {
        $this->assertTrue($this->appAdmin()->can('updateReviewers', $this->makeSubmission()));

        $submission = $this->makeSubmission();
        $coordinator = User::factory()->create();
        $this->attachToSubmission($coordinator, $submission, ScopedRole::ReviewCoordinator->toSlug());
        $this->assertTrue($coordinator->can('updateReviewers', $submission));
    }

    public function testUpdateReviewersDeniesSubmitterAndReviewer(): void
    {
        foreach ([ScopedRole::Submitter->toSlug(), ScopedRole::Reviewer->toSlug()] as $roleId) {
            $submission = $this->makeSubmission();
            $user = User::factory()->create();
            $this->attachToSubmission($user, $submission, $roleId);

            $this->assertFalse($user->can('updateReviewers', $submission), "role_id $roleId");
        }
    }

    // ---- updateReviewCoordinators (admin only) ------------------------------

    public function testUpdateReviewCoordinatorsAllowsAdminRolesOnly(): void
    {
        $this->assertTrue($this->appAdmin()->can('updateReviewCoordinators', $this->makeSubmission()));

        $submission = $this->makeSubmission();
        $editor = User::factory()->create();
        $this->attachToPublication($editor, $submission->publication, ScopedRole::Editor->toSlug());
        $this->assertTrue($editor->can('updateReviewCoordinators', $submission));
    }

    public function testUpdateReviewCoordinatorsDeniesReviewCoordinator(): void
    {
        $submission = $this->makeSubmission();
        $coordinator = User::factory()->create();
        $this->attachToSubmission($coordinator, $submission, ScopedRole::ReviewCoordinator->toSlug());

        $this->assertFalse($coordinator->can('updateReviewCoordinators', $submission));
    }

    // ---- updateStatus -------------------------------------------------------

    public function testUpdateStatusAllowsAdminAndReviewCoordinator(): void
    {
        $this->assertTrue($this->appAdmin()->can('updateStatus', $this->makeSubmission()));

        $submission = $this->makeSubmission();
        $coordinator = User::factory()->create();
        $this->attachToSubmission($coordinator, $submission, ScopedRole::ReviewCoordinator->toSlug());
        $this->assertTrue($coordinator->can('updateStatus', $submission));
    }

    public function testUpdateStatusAllowsSubmitterOnlyWhileDraft(): void
    {
        $draft = $this->makeSubmission(Submission::DRAFT);
        $submitterDraft = User::factory()->create();
        $this->attachToSubmission($submitterDraft, $draft, ScopedRole::Submitter->toSlug());
        $this->assertTrue($submitterDraft->can('updateStatus', $draft));

        $submitted = $this->makeSubmission(Submission::INITIALLY_SUBMITTED);
        $submitterSubmitted = User::factory()->create();
        $this->attachToSubmission($submitterSubmitted, $submitted, ScopedRole::Submitter->toSlug());
        $this->assertFalse($submitterSubmitted->can('updateStatus', $submitted));
    }

    public function testUpdateStatusDeniesReviewer(): void
    {
        $submission = $this->makeSubmission();
        $reviewer = User::factory()->create();
        $this->attachToSubmission($reviewer, $submission, ScopedRole::Reviewer->toSlug());

        $this->assertFalse($reviewer->can('updateStatus', $submission));
    }

    // ---- view (any submission role grants it, via the matrix) ---------------

    public function testViewAllowsAdminAndAnySubmissionRole(): void
    {
        $this->assertTrue($this->appAdmin()->can('view', $this->makeSubmission()));

        $submission = $this->makeSubmission();
        $reviewer = User::factory()->create();
        $this->attachToSubmission($reviewer, $submission, ScopedRole::Reviewer->toSlug());
        $this->assertTrue($reviewer->can('view', $submission));
    }

    public function testViewDeniesUnaffiliatedUser(): void
    {
        $this->assertFalse(User::factory()->create()->can('view', $this->makeSubmission()));
    }

    // ---- update (the work: author-only, draft-only) -------------------------

    public function testUpdateAllowsSubmitterOnlyWhileDraft(): void
    {
        $draft = $this->makeSubmission(Submission::DRAFT);
        $submitterDraft = User::factory()->create();
        $this->attachToSubmission($submitterDraft, $draft, ScopedRole::Submitter->toSlug());
        $this->assertTrue($submitterDraft->can('updateContent', $draft));

        $submitted = $this->makeSubmission(Submission::INITIALLY_SUBMITTED);
        $submitterSubmitted = User::factory()->create();
        $this->attachToSubmission($submitterSubmitted, $submitted, ScopedRole::Submitter->toSlug());
        $this->assertFalse($submitterSubmitted->can('updateContent', $submitted));
    }

    public function testUpdateDeniesReviewerAndUnaffiliated(): void
    {
        $draft = $this->makeSubmission(Submission::DRAFT);
        $reviewer = User::factory()->create();
        $this->attachToSubmission($reviewer, $draft, ScopedRole::Reviewer->toSlug());

        // The manuscript-edit hole closed: a reviewer holds no `update`.
        $this->assertFalse($reviewer->can('updateContent', $draft));
        $this->assertFalse(User::factory()->create()->can('updateContent', $draft));
    }

    // ---- legacyUpdate (transitional god-mutation umbrella; broad) ------------

    public function testLegacyUpdateAllowsAnySubmissionRoleAndDeniesUnaffiliated(): void
    {
        $submission = $this->makeSubmission();
        $reviewer = User::factory()->create();
        $this->attachToSubmission($reviewer, $submission, ScopedRole::Reviewer->toSlug());
        $this->assertTrue($reviewer->can('legacyUpdate', $submission));

        $this->assertFalse(User::factory()->create()->can('legacyUpdate', $this->makeSubmission()));
    }

    // ---- review (manuscript access + comment, while reviewable) --------------

    public function testReviewAllowsAnySubmissionRoleOnlyWhileReviewable(): void
    {
        $underReview = $this->makeSubmission(Submission::UNDER_REVIEW);
        $reviewer = User::factory()->create();
        $this->attachToSubmission($reviewer, $underReview, ScopedRole::Reviewer->toSlug());
        $this->assertTrue($reviewer->can('review', $underReview));

        $draft = $this->makeSubmission(Submission::DRAFT);
        $reviewerDraft = User::factory()->create();
        $this->attachToSubmission($reviewerDraft, $draft, ScopedRole::Reviewer->toSlug());
        $this->assertFalse($reviewerDraft->can('review', $draft));
    }

    public function testReviewDeniesUnaffiliatedUser(): void
    {
        $this->assertFalse(
            User::factory()->create()->can('review', $this->makeSubmission(Submission::UNDER_REVIEW))
        );
    }

    // ---- submit (the author's forward action, draft-only) -------------------

    public function testSubmitAllowsSubmitterOnlyWhileDraft(): void
    {
        $draft = $this->makeSubmission(Submission::DRAFT);
        $submitter = User::factory()->create();
        $this->attachToSubmission($submitter, $draft, ScopedRole::Submitter->toSlug());
        $this->assertTrue($submitter->can('submit', $draft));

        $submitted = $this->makeSubmission(Submission::INITIALLY_SUBMITTED);
        $submitterSubmitted = User::factory()->create();
        $this->attachToSubmission($submitterSubmitted, $submitted, ScopedRole::Submitter->toSlug());
        $this->assertFalse($submitterSubmitted->can('submit', $submitted));
    }

    public function testSubmitDeniesReviewerAndCoordinator(): void
    {
        foreach ([ScopedRole::Reviewer->toSlug(), ScopedRole::ReviewCoordinator->toSlug()] as $roleId) {
            $draft = $this->makeSubmission(Submission::DRAFT);
            $user = User::factory()->create();
            $this->attachToSubmission($user, $draft, $roleId);

            $this->assertFalse($user->can('submit', $draft), "role_id $roleId");
        }
    }

    // ---- inline comment ownership -------------------------------------------

    public function testUpdateInlineCommentsAllowsOwnerAndDeniesOther(): void
    {
        $submission = $this->makeSubmission();
        $owner = User::factory()->create();
        $comment = InlineComment::factory()->create([
            'submission_id' => $submission->id,
            'created_by' => $owner->id,
        ]);
        $args = ['inlineComments' => ['update' => [['id' => $comment->id]]]];

        $this->assertTrue($owner->can('updateInlineComments', [$submission, $args]));
        $this->assertFalse(User::factory()->create()->can('updateInlineComments', [$submission, $args]));
    }

    public function testUpdateInlineCommentsAllowsWhenNoUpdatePayload(): void
    {
        $submission = $this->makeSubmission();

        $this->assertTrue(User::factory()->create()->can('updateInlineComments', [$submission, []]));
    }

    public function testDeleteInlineCommentAllowsOwnerAndDeniesOther(): void
    {
        $submission = $this->makeSubmission();
        $owner = User::factory()->create();
        $comment = InlineComment::factory()->create([
            'submission_id' => $submission->id,
            'created_by' => $owner->id,
        ]);
        $args = ['comment_id' => $comment->id];

        $this->assertTrue($owner->can('deleteInlineComment', [$submission, $args]));
        $this->assertFalse(User::factory()->create()->can('deleteInlineComment', [$submission, $args]));
    }

    // ---- overall comment ownership ------------------------------------------

    public function testUpdateOverallCommentsAllowsOwnerAndDeniesOther(): void
    {
        $submission = $this->makeSubmission();
        $owner = User::factory()->create();
        $comment = OverallComment::factory()->create([
            'submission_id' => $submission->id,
            'created_by' => $owner->id,
        ]);
        $args = ['overallComments' => ['update' => [['id' => $comment->id]]]];

        $this->assertTrue($owner->can('updateOverallComments', [$submission, $args]));
        $this->assertFalse(User::factory()->create()->can('updateOverallComments', [$submission, $args]));
    }

    public function testDeleteOverallCommentAllowsOwnerAndDeniesOther(): void
    {
        $submission = $this->makeSubmission();
        $owner = User::factory()->create();
        $comment = OverallComment::factory()->create([
            'submission_id' => $submission->id,
            'created_by' => $owner->id,
        ]);
        $args = ['comment_id' => $comment->id];

        $this->assertTrue($owner->can('deleteOverallComment', [$submission, $args]));
        $this->assertFalse(User::factory()->create()->can('deleteOverallComment', [$submission, $args]));
    }
}
