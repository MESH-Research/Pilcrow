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
 * CommentPolicy resolves the comment-scoped update / delete abilities through
 * the {@see \App\Auth\ScopedAbilityResolver}: an author may revise or retract
 * their own comment while the submission is reviewable (and only then), while an
 * application administrator moderates any comment unconditionally. The gate is
 * the comment's own — independent of who may CREATE comments — so it is asserted
 * here against the comment models directly.
 */
class CommentPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param int $status
     * @return \App\Models\Submission
     */
    private function submission(int $status = Submission::UNDER_REVIEW): Submission
    {
        return Submission::factory()
            ->for(Publication::factory()->create())
            ->create(['status' => $status]);
    }

    /**
     * A user holding the reviewer role on the submission.
     *
     * @param \App\Models\Submission $submission
     * @return \App\Models\User
     */
    private function reviewer(Submission $submission): User
    {
        $user = User::factory()->create();
        $submission->users()->attach($user->id, ['role' => ScopedRole::Reviewer->toSlug()]);

        return $user;
    }

    /**
     * @param \App\Models\Submission $submission
     * @param \App\Models\User $author
     * @return \App\Models\InlineComment
     */
    private function inlineBy(Submission $submission, User $author): InlineComment
    {
        return InlineComment::withoutEvents(fn() => InlineComment::factory()->create([
            'submission_id' => $submission->id,
            'created_by' => $author->id,
            'updated_by' => $author->id,
            'style_criteria' => [],
        ]));
    }

    public function testAuthorCanEditAndDeleteOwnCommentWhileReviewable(): void
    {
        $submission = $this->submission(Submission::UNDER_REVIEW);
        $author = $this->reviewer($submission);
        $comment = $this->inlineBy($submission, $author);

        $this->assertTrue($author->can('update', $comment));
        $this->assertTrue($author->can('delete', $comment));
    }

    public function testAuthorCannotEditOwnCommentOnceReviewCloses(): void
    {
        $submission = $this->submission(Submission::AWAITING_DECISION);
        $author = $this->reviewer($submission);
        $comment = $this->inlineBy($submission, $author);

        $this->assertFalse($author->can('update', $comment));
        $this->assertFalse($author->can('delete', $comment));
    }

    public function testNonAuthorCannotEditAnothersComment(): void
    {
        $submission = $this->submission(Submission::UNDER_REVIEW);
        $author = $this->reviewer($submission);
        $comment = $this->inlineBy($submission, $author);
        $other = $this->reviewer($submission);

        $this->assertFalse($other->can('update', $comment));
        $this->assertFalse($other->can('delete', $comment));
    }

    public function testApplicationAdministratorModeratesAnyComment(): void
    {
        // Non-author, and the submission is past review — the admin short-circuit
        // moderates regardless of both conditions.
        $submission = $this->submission(Submission::AWAITING_DECISION);
        $author = $this->reviewer($submission);
        $comment = $this->inlineBy($submission, $author);

        $admin = User::factory()->create();
        $admin->assignRole(GlobalRole::ApplicationAdministrator);

        $this->assertTrue($admin->can('update', $comment));
        $this->assertTrue($admin->can('delete', $comment));
    }

    public function testGateAppliesToOverallCommentsToo(): void
    {
        $submission = $this->submission(Submission::UNDER_REVIEW);
        $author = $this->reviewer($submission);
        $comment = OverallComment::withoutEvents(fn() => OverallComment::factory()->create([
            'submission_id' => $submission->id,
            'created_by' => $author->id,
            'updated_by' => $author->id,
        ]));

        $this->assertTrue($author->can('update', $comment));
        $this->assertFalse($this->reviewer($submission)->can('update', $comment));
    }
}
