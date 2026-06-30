<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Auth\Roles\ScopedRole;
use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;
use Tests\TestFactory;

/**
 * Feature coverage for the intent-shaped submission mutations, each authorized
 * by a single plain @can against the corrected ability matrix. The deprecated
 * `updateSubmission` god-mutation keeps its own coverage in {@see SubmissionTest}
 * / {@see SubmissionCommentTest}.
 */
class SubmissionIntentMutationsTest extends ApiTestCase
{
    use RefreshDatabase;
    use TestFactory;

    /**
     * Build a submission and a user holding $role on it (submission pivot).
     *
     * @param \App\Auth\Roles\ScopedRole $role
     * @param int $status
     * @return array{0: \App\Models\Submission, 1: \App\Models\User}
     */
    private function submissionWithSubmissionRole(ScopedRole $role, int $status = Submission::UNDER_REVIEW): array
    {
        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->create(['status' => $status]);
        $user = User::factory()->create();
        $submission->users()->attach($user->id, ['role' => $role->toSlug()]);

        return [$submission, $user];
    }

    // ---- updateSubmissionContent: title rides the content mutation ----------
    // Body and title are both the author's content under one `update` ability
    // (author-only, draft-only), so they share updateSubmissionContent.

    public function testUpdateSubmissionContentEditsTitleForSubmitterOnDraft(): void
    {
        [$submission, $submitter] = $this->submissionWithSubmissionRole(ScopedRole::Submitter, Submission::DRAFT);
        $this->actingAs($submitter);

        $response = $this->graphQL(
            'mutation ($id: ID!, $title: String!) {
                updateSubmissionContent(input: { id: $id, title: $title }) { id title }
            }',
            ['id' => $submission->id, 'title' => 'A revised title']
        );

        $response->assertJsonPath('data.updateSubmissionContent.title', 'A revised title');
        $this->assertSame('A revised title', $submission->fresh()->title);
    }

    public function testUpdateSubmissionContentEditsBodyAndTitleTogether(): void
    {
        [$submission, $submitter] = $this->submissionWithSubmissionRole(ScopedRole::Submitter, Submission::DRAFT);
        $this->actingAs($submitter);

        $response = $this->graphQL(
            'mutation ($id: ID!, $content: String!, $title: String!) {
                updateSubmissionContent(input: { id: $id, content: $content, title: $title }) {
                    id
                    title
                    content { data }
                }
            }',
            ['id' => $submission->id, 'content' => 'New body', 'title' => 'New title']
        );

        $response->assertJsonPath('data.updateSubmissionContent.title', 'New title');
        $response->assertJsonPath('data.updateSubmissionContent.content.data', '<p>New body</p>');
    }

    public function testUpdateSubmissionContentTitleDeniesReviewer(): void
    {
        [$submission, $reviewer] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::DRAFT);
        $this->actingAs($reviewer);

        $response = $this->graphQL(
            'mutation ($id: ID!, $title: String!) {
                updateSubmissionContent(input: { id: $id, title: $title }) { id title }
            }',
            ['id' => $submission->id, 'title' => 'Reviewer cannot do this']
        );

        $response->assertJsonPath('errors.0.message', 'UNAUTHORIZED');
        $response->assertJsonPath('data.updateSubmissionContent', null);
    }

    public function testUpdateSubmissionContentTitleDeniesSubmitterAfterDraft(): void
    {
        [$submission, $submitter] = $this->submissionWithSubmissionRole(
            ScopedRole::Submitter,
            Submission::INITIALLY_SUBMITTED
        );
        $this->actingAs($submitter);

        $response = $this->graphQL(
            'mutation ($id: ID!, $title: String!) {
                updateSubmissionContent(input: { id: $id, title: $title }) { id title }
            }',
            ['id' => $submission->id, 'title' => 'Too late']
        );

        $response->assertJsonPath('errors.0.message', 'UNAUTHORIZED');
    }

    // ---- submitSubmission (Submit: submitter, draft-only) -------------------

    public function testSubmitSubmissionMovesDraftToInitiallySubmitted(): void
    {
        [$submission, $submitter] = $this->submissionWithSubmissionRole(ScopedRole::Submitter, Submission::DRAFT);
        $this->actingAs($submitter);

        $response = $this->graphQL(
            'mutation ($id: ID!) { submitSubmission(submission_id: $id) { id status } }',
            ['id' => $submission->id]
        );

        $response->assertJsonPath('data.submitSubmission.status', 'INITIALLY_SUBMITTED');
        $this->assertSame(Submission::INITIALLY_SUBMITTED, $submission->fresh()->status);
    }

    public function testSubmitSubmissionDeniesReviewer(): void
    {
        [$submission, $reviewer] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::DRAFT);
        $this->actingAs($reviewer);

        $response = $this->graphQL(
            'mutation ($id: ID!) { submitSubmission(submission_id: $id) { id status } }',
            ['id' => $submission->id]
        );

        $response->assertJsonPath('errors.0.message', 'UNAUTHORIZED');
        $this->assertSame(Submission::DRAFT, $submission->fresh()->status);
    }

    // ---- changeSubmissionStatus (UpdateStatus: RC+, absolute) ---------------

    public function testChangeSubmissionStatusAllowsReviewCoordinatorAndSetsComment(): void
    {
        [$submission, $coordinator] = $this->submissionWithSubmissionRole(
            ScopedRole::ReviewCoordinator,
            Submission::AWAITING_REVIEW
        );
        $this->actingAs($coordinator);

        $response = $this->graphQL(
            'mutation ($id: ID!, $status: SubmissionStatus!, $comment: String) {
                changeSubmissionStatus(
                    input: { id: $id, status: $status, status_change_comment: $comment }
                ) {
                    id
                    status
                    status_change_comment
                }
            }',
            [
                'id' => $submission->id,
                'status' => 'UNDER_REVIEW',
                'comment' => 'Opening review.',
            ]
        );

        $response->assertJsonPath('data.changeSubmissionStatus.status', 'UNDER_REVIEW');
        $response->assertJsonPath('data.changeSubmissionStatus.status_change_comment', 'Opening review.');
    }

    public function testChangeSubmissionStatusDeniesReviewer(): void
    {
        [$submission, $reviewer] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::UNDER_REVIEW);
        $this->actingAs($reviewer);

        $response = $this->graphQL(
            'mutation ($id: ID!, $status: SubmissionStatus!) {
                changeSubmissionStatus(input: { id: $id, status: $status }) { id status }
            }',
            ['id' => $submission->id, 'status' => 'REJECTED']
        );

        $response->assertJsonPath('errors.0.message', 'UNAUTHORIZED');
    }

    // ---- updateSubmissionReviewers / Submitters / ReviewCoordinators --------

    public function testUpdateSubmissionReviewersAllowsReviewCoordinator(): void
    {
        [$submission, $coordinator] = $this->submissionWithSubmissionRole(ScopedRole::ReviewCoordinator);
        $this->actingAs($coordinator);
        $newReviewer = User::factory()->create();

        $response = $this->graphQL(
            'mutation ($id: ID!, $connect: [ID!]) {
                updateSubmissionReviewers(input: { id: $id, reviewers: { connect: $connect } }) {
                    id
                    reviewers { id }
                }
            }',
            ['id' => $submission->id, 'connect' => [$newReviewer->id]]
        );

        $response->assertJsonPath('data.updateSubmissionReviewers.reviewers.0.id', (string)$newReviewer->id);
    }

    public function testUpdateSubmissionReviewersDeniesSubmitter(): void
    {
        [$submission, $submitter] = $this->submissionWithSubmissionRole(ScopedRole::Submitter, Submission::DRAFT);
        $this->actingAs($submitter);
        $newReviewer = User::factory()->create();

        $response = $this->graphQL(
            'mutation ($id: ID!, $connect: [ID!]) {
                updateSubmissionReviewers(input: { id: $id, reviewers: { connect: $connect } }) { id }
            }',
            ['id' => $submission->id, 'connect' => [$newReviewer->id]]
        );

        $response->assertJsonPath('errors.0.message', 'UNAUTHORIZED');
    }

    public function testUpdateSubmissionSubmittersAllowsSubmitter(): void
    {
        [$submission, $submitter] = $this->submissionWithSubmissionRole(ScopedRole::Submitter, Submission::DRAFT);
        $this->actingAs($submitter);
        $coSubmitter = User::factory()->create();

        $response = $this->graphQL(
            'mutation ($id: ID!, $connect: [ID!]) {
                updateSubmissionSubmitters(input: { id: $id, submitters: { connect: $connect } }) {
                    id
                    submitters { id }
                }
            }',
            ['id' => $submission->id, 'connect' => [$coSubmitter->id]]
        );

        $ids = array_column($response->json('data.updateSubmissionSubmitters.submitters'), 'id');
        $this->assertContains((string)$coSubmitter->id, $ids);
    }

    public function testUpdateSubmissionReviewCoordinatorsAllowsEditor(): void
    {
        $publication = Publication::factory()->create();
        $submission = Submission::factory()->for($publication)->create(['status' => Submission::UNDER_REVIEW]);
        $editor = User::factory()->create();
        $editor->publications()->attach($publication->id, ['role' => ScopedRole::Editor->toSlug()]);
        $this->actingAs($editor);
        $newCoordinator = User::factory()->create();

        $response = $this->graphQL(
            'mutation ($id: ID!, $connect: [ID!]) {
                updateSubmissionReviewCoordinators(
                    input: { id: $id, review_coordinators: { connect: $connect } }
                ) {
                    id
                    review_coordinators { id }
                }
            }',
            ['id' => $submission->id, 'connect' => [$newCoordinator->id]]
        );

        $response->assertJsonPath(
            'data.updateSubmissionReviewCoordinators.review_coordinators.0.id',
            (string)$newCoordinator->id
        );
    }

    public function testUpdateSubmissionReviewCoordinatorsDeniesReviewCoordinator(): void
    {
        [$submission, $coordinator] = $this->submissionWithSubmissionRole(ScopedRole::ReviewCoordinator);
        $this->actingAs($coordinator);
        $newCoordinator = User::factory()->create();

        $response = $this->graphQL(
            'mutation ($id: ID!, $connect: [ID!]) {
                updateSubmissionReviewCoordinators(
                    input: { id: $id, review_coordinators: { connect: $connect } }
                ) { id }
            }',
            ['id' => $submission->id, 'connect' => [$newCoordinator->id]]
        );

        $response->assertJsonPath('errors.0.message', 'UNAUTHORIZED');
    }

    // ---- createInlineComment / createOverallComment (Review: reviewable) ----

    public function testCreateInlineCommentAllowsReviewerWhileUnderReview(): void
    {
        [$submission, $reviewer] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::UNDER_REVIEW);
        $this->actingAs($reviewer);

        $response = $this->graphQL(
            'mutation ($id: ID!) {
                createInlineComment(input: { submission_id: $id, content: "Inline note", from: 1, to: 5 }) {
                    id
                    inline_comments { content from to }
                }
            }',
            ['id' => $submission->id]
        );

        $response->assertJsonPath('data.createInlineComment.inline_comments.0.content', 'Inline note');
        $response->assertJsonPath('data.createInlineComment.inline_comments.0.from', 1);
    }

    public function testCreateInlineCommentDeniesWhenNotReviewable(): void
    {
        [$submission, $reviewer] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::DRAFT);
        $this->actingAs($reviewer);

        $response = $this->graphQL(
            'mutation ($id: ID!) {
                createInlineComment(input: { submission_id: $id, content: "Too early" }) { id }
            }',
            ['id' => $submission->id]
        );

        $response->assertJsonPath('errors.0.message', 'UNAUTHORIZED');
    }

    public function testCreateInlineCommentReply(): void
    {
        [$submission, $reviewer] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::UNDER_REVIEW);
        // Act before creating the parent: created_by is set from the acting user
        // (it is not mass-assignable), and the styleCriteria accessor needs a list.
        $this->actingAs($reviewer);
        $parent = $submission->inlineComments()->create([
            'content' => 'Parent comment',
            'style_criteria' => [],
        ]);

        $response = $this->graphQL(
            'mutation ($id: ID!, $parent: ID!, $replyTo: ID!) {
                createInlineComment(input: {
                    submission_id: $id
                    content: "A reply"
                    parent_id: $parent
                    reply_to_id: $replyTo
                }) {
                    id
                    inline_comments { replies { content parent_id reply_to_id } }
                }
            }',
            ['id' => $submission->id, 'parent' => $parent->id, 'replyTo' => $parent->id]
        );

        $response->assertJsonPath(
            'data.createInlineComment.inline_comments.0.replies.0.content',
            'A reply'
        );
    }

    public function testCreateInlineCommentReplyRejectsIncoherentThread(): void
    {
        [$submission, $reviewer] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::UNDER_REVIEW);
        $this->actingAs($reviewer);

        // parent_id without reply_to_id is not a coherent reply.
        $response = $this->graphQL(
            'mutation ($id: ID!, $parent: ID!) {
                createInlineComment(input: { submission_id: $id, content: "Bad reply", parent_id: $parent }) { id }
            }',
            ['id' => $submission->id, 'parent' => 999999]
        );

        $this->assertNotEmpty($response->json('errors'));
        $response->assertJsonPath('data.createInlineComment', null);
    }

    public function testCreateOverallCommentAllowsReviewerWhileUnderReview(): void
    {
        [$submission, $reviewer] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::UNDER_REVIEW);
        $this->actingAs($reviewer);

        $response = $this->graphQL(
            'mutation ($id: ID!) {
                createOverallComment(input: { submission_id: $id, content: "Overall note" }) {
                    id
                    overall_comments { content }
                }
            }',
            ['id' => $submission->id]
        );

        $response->assertJsonPath('data.createOverallComment.overall_comments.0.content', 'Overall note');
    }

    public function testCreateOverallCommentDeniesWhenNotReviewable(): void
    {
        [$submission, $reviewer] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::AWAITING_DECISION);
        $this->actingAs($reviewer);

        $response = $this->graphQL(
            'mutation ($id: ID!) {
                createOverallComment(input: { submission_id: $id, content: "Too late" }) { id }
            }',
            ['id' => $submission->id]
        );

        $response->assertJsonPath('errors.0.message', 'UNAUTHORIZED');
    }

    // ---- updateInlineComment / updateOverallComment (author-only) -----------
    // Editing a comment is gated by ownership, the same gate as delete — not by
    // `review`, so the author can fix their wording regardless of status.

    public function testUpdateInlineCommentAllowsAuthor(): void
    {
        [$submission, $reviewer] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::UNDER_REVIEW);
        // Act before creating: created_by is set from the acting user.
        $this->actingAs($reviewer);
        $comment = $submission->inlineComments()->create([
            'content' => 'Original',
            'style_criteria' => [],
            'from' => 1,
            'to' => 5,
        ]);

        $response = $this->graphQL(
            'mutation ($id: ID!, $comment: ID!) {
                updateInlineComment(input: { submission_id: $id, comment_id: $comment, content: "Edited" }) {
                    id
                    inline_comments { content }
                }
            }',
            ['id' => $submission->id, 'comment' => $comment->id]
        );

        $response->assertJsonPath('data.updateInlineComment.inline_comments.0.content', 'Edited');
        $this->assertSame('Edited', $comment->fresh()->content);
    }

    public function testUpdateInlineCommentDeniesNonAuthor(): void
    {
        [$submission, $author] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::UNDER_REVIEW);
        $this->actingAs($author);
        $comment = $submission->inlineComments()->create([
            'content' => 'Original',
            'style_criteria' => [],
        ]);

        // A different reviewer on the same submission may not edit another's comment.
        $other = User::factory()->create();
        $submission->users()->attach($other->id, ['role' => ScopedRole::Reviewer->toSlug()]);
        $this->actingAs($other);

        $response = $this->graphQL(
            'mutation ($id: ID!, $comment: ID!) {
                updateInlineComment(input: { submission_id: $id, comment_id: $comment, content: "Hijack" }) { id }
            }',
            ['id' => $submission->id, 'comment' => $comment->id]
        );

        $response->assertJsonPath('errors.0.message', 'UNAUTHORIZED');
        $this->assertSame('Original', $comment->fresh()->content);
    }

    public function testUpdateInlineCommentEditsAReply(): void
    {
        [$submission, $reviewer] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::UNDER_REVIEW);
        $this->actingAs($reviewer);
        $parent = $submission->inlineComments()->create([
            'content' => 'Parent comment',
            'style_criteria' => [],
        ]);
        // A reply is just an inline-comment row carrying parent_id/reply_to_id,
        // so the edit folds in: it is found and updated by id like any other.
        $reply = $submission->inlineComments()->create([
            'content' => 'Original reply',
            'style_criteria' => [],
            'parent_id' => $parent->id,
            'reply_to_id' => $parent->id,
        ]);

        $response = $this->graphQL(
            'mutation ($id: ID!, $comment: ID!) {
                updateInlineComment(input: { submission_id: $id, comment_id: $comment, content: "Edited reply" }) { id }
            }',
            ['id' => $submission->id, 'comment' => $reply->id]
        );

        $response->assertJsonPath('data.updateInlineComment.id', (string)$submission->id);
        $this->assertSame('Edited reply', $reply->fresh()->content);
    }

    public function testUpdateOverallCommentAllowsAuthor(): void
    {
        [$submission, $reviewer] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::UNDER_REVIEW);
        $this->actingAs($reviewer);
        $comment = $submission->overallComments()->create(['content' => 'Original']);

        $response = $this->graphQL(
            'mutation ($id: ID!, $comment: ID!) {
                updateOverallComment(input: { submission_id: $id, comment_id: $comment, content: "Edited" }) {
                    id
                    overall_comments { content }
                }
            }',
            ['id' => $submission->id, 'comment' => $comment->id]
        );

        $response->assertJsonPath('data.updateOverallComment.overall_comments.0.content', 'Edited');
        $this->assertSame('Edited', $comment->fresh()->content);
    }

    public function testUpdateOverallCommentDeniesNonAuthor(): void
    {
        [$submission, $author] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::UNDER_REVIEW);
        $this->actingAs($author);
        $comment = $submission->overallComments()->create(['content' => 'Original']);

        $other = User::factory()->create();
        $submission->users()->attach($other->id, ['role' => ScopedRole::Reviewer->toSlug()]);
        $this->actingAs($other);

        $response = $this->graphQL(
            'mutation ($id: ID!, $comment: ID!) {
                updateOverallComment(input: { submission_id: $id, comment_id: $comment, content: "Hijack" }) { id }
            }',
            ['id' => $submission->id, 'comment' => $comment->id]
        );

        $response->assertJsonPath('errors.0.message', 'UNAUTHORIZED');
        $this->assertSame('Original', $comment->fresh()->content);
    }
}
