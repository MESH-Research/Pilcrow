<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Auth\Roles\ScopedRole;
use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\ApiTestCase;
use Tests\TestFactory;

/**
 * Feature coverage for the intent-shaped submission mutations, each authorized
 * by a single plain @can against the corrected ability matrix.
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

    public function testUpdateSubmissionSubmittersDeniesReviewer(): void
    {
        [$submission, $reviewer] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::UNDER_REVIEW);
        $this->actingAs($reviewer);
        $target = User::factory()->create();

        $response = $this->graphQL(
            'mutation ($id: ID!, $connect: [ID!]) {
                updateSubmissionSubmitters(input: { id: $id, submitters: { connect: $connect } }) { id }
            }',
            ['id' => $submission->id, 'connect' => [$target->id]]
        );

        $response->assertJsonPath('errors.0.message', 'UNAUTHORIZED');
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
        // A REAL parent, so no foreign-key error can stand in for the validator:
        // parent_id present without reply_to_id is an incoherent (XOR) reply and
        // must be rejected by CommentReplyCoherence itself.
        $parent = $submission->inlineComments()->create(['content' => 'Parent', 'style_criteria' => []]);

        $response = $this->graphQL(
            'mutation ($id: ID!, $parent: ID!) {
                createInlineComment(input: { submission_id: $id, content: "Bad reply", parent_id: $parent }) { id }
            }',
            ['id' => $submission->id, 'parent' => $parent->id]
        );

        // The validation error — not an incidental DB error — is what rejects it.
        $response->assertGraphQLValidationError('input.submission_id', 'Invalid reply.');
        $response->assertJsonPath('data.createInlineComment', null);
        // Only the parent exists; no incoherent reply row was written.
        $this->assertSame(1, $submission->inlineComments()->count());
    }

    public function testCreateInlineCommentRejectsReplyToOutsideThread(): void
    {
        [$submission, $reviewer] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::UNDER_REVIEW);
        $this->actingAs($reviewer);
        $parent = $submission->inlineComments()->create(['content' => 'Parent', 'style_criteria' => []]);
        $unrelated = $submission->inlineComments()->create(['content' => 'Unrelated', 'style_criteria' => []]);

        // reply_to_id must be the parent or one of its replies; an unrelated
        // comment is not a coherent thread.
        $response = $this->graphQL(
            'mutation ($id: ID!, $parent: ID!, $replyTo: ID!) {
                createInlineComment(input: { submission_id: $id, content: "bad", parent_id: $parent, reply_to_id: $replyTo }) { id }
            }',
            ['id' => $submission->id, 'parent' => $parent->id, 'replyTo' => $unrelated->id]
        );

        $response->assertGraphQLValidationError('input.submission_id', 'Invalid reply.');
        $response->assertJsonPath('data.createInlineComment', null);
        // No third row was created.
        $this->assertSame(2, $submission->inlineComments()->count());
    }

    public function testCreateInlineCommentRejectsParentFromAnotherSubmission(): void
    {
        [$submission, $reviewer] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::UNDER_REVIEW);
        $this->actingAs($reviewer);
        $other = Submission::factory()
            ->for(Publication::factory()->create())
            ->create(['status' => Submission::UNDER_REVIEW]);
        $foreignParent = $other->inlineComments()->create(['content' => 'Foreign', 'style_criteria' => []]);

        // The whole thread must live on the same submission.
        $response = $this->graphQL(
            'mutation ($id: ID!, $parent: ID!, $replyTo: ID!) {
                createInlineComment(input: { submission_id: $id, content: "bad", parent_id: $parent, reply_to_id: $replyTo }) { id }
            }',
            ['id' => $submission->id, 'parent' => $foreignParent->id, 'replyTo' => $foreignParent->id]
        );

        $response->assertGraphQLValidationError('input.submission_id', 'Invalid reply.');
        $response->assertJsonPath('data.createInlineComment', null);
    }

    public function testCreateInlineCommentAllowsReplyTargetingASiblingReply(): void
    {
        [$submission, $reviewer] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::UNDER_REVIEW);
        $this->actingAs($reviewer);
        $parent = $submission->inlineComments()->create(['content' => 'Parent', 'style_criteria' => []]);
        $firstReply = $submission->inlineComments()->create([
            'content' => 'First reply',
            'style_criteria' => [],
            'parent_id' => $parent->id,
            'reply_to_id' => $parent->id,
        ]);

        // Replying to an existing reply of the parent is coherent.
        $response = $this->graphQL(
            'mutation ($id: ID!, $parent: ID!, $replyTo: ID!) {
                createInlineComment(input: { submission_id: $id, content: "second", parent_id: $parent, reply_to_id: $replyTo }) { id }
            }',
            ['id' => $submission->id, 'parent' => $parent->id, 'replyTo' => $firstReply->id]
        );

        $response->assertJsonPath('data.createInlineComment.id', (string)$submission->id);
        // The new reply joined the parent's thread alongside the first.
        $this->assertSame(2, $parent->replies()->count());
    }

    public function testCreateOverallCommentRejectsReplyToOutsideThread(): void
    {
        [$submission, $reviewer] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::UNDER_REVIEW);
        $this->actingAs($reviewer);
        $parent = $submission->overallComments()->create(['content' => 'Parent']);
        $unrelated = $submission->overallComments()->create(['content' => 'Unrelated']);

        $response = $this->graphQL(
            'mutation ($id: ID!, $parent: ID!, $replyTo: ID!) {
                createOverallComment(input: { submission_id: $id, content: "bad", parent_id: $parent, reply_to_id: $replyTo }) { id }
            }',
            ['id' => $submission->id, 'parent' => $parent->id, 'replyTo' => $unrelated->id]
        );

        $response->assertGraphQLValidationError('input.submission_id', 'Invalid reply.');
        $response->assertJsonPath('data.createOverallComment', null);
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

    public function testUpdateInlineCommentDeniesAuthorAfterReviewCloses(): void
    {
        [$submission, $reviewer] = $this->submissionWithSubmissionRole(ScopedRole::Reviewer, Submission::UNDER_REVIEW);
        $this->actingAs($reviewer);
        $comment = $submission->inlineComments()->create([
            'content' => 'Original',
            'style_criteria' => [],
        ]);

        // Review closes — the author's edit window closes with it.
        $submission->update(['status' => Submission::AWAITING_DECISION]);

        $response = $this->graphQL(
            'mutation ($id: ID!, $comment: ID!) {
                updateInlineComment(input: { submission_id: $id, comment_id: $comment, content: "Too late" }) { id }
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

    // ---- Role matrices ------------------------------------------------------
    // Cross-role coverage for the editorial mutations: global admin, the
    // publication chain (incl. an admin of a DIFFERENT publication), and the
    // submission roles.

    /**
     * Build a submission and act as a user holding $actor on it.
     *
     * $actor is one of: app_admin, publication_admin, foreign_publication_admin,
     * editor, review_coordinator, reviewer, submitter.
     *
     * @param string $actor
     * @param int $status
     * @return array{0: \App\Models\Submission, 1: \App\Models\User}
     */
    private function actAs(string $actor, int $status = Submission::UNDER_REVIEW): array
    {
        $publication = Publication::factory()->create();
        $submission = Submission::factory()->for($publication)->create(['status' => $status]);

        switch ($actor) {
            case 'app_admin':
                $user = $this->beAppAdmin();
                break;
            case 'publication_admin':
            case 'editor':
                $user = User::factory()->create();
                $role = $actor === 'editor' ? ScopedRole::Editor : ScopedRole::PublicationAdmin;
                $user->publications()->attach($publication->id, ['role' => $role->toSlug()]);
                break;
            case 'foreign_publication_admin':
                $user = User::factory()->create();
                $user->publications()->attach(
                    Publication::factory()->create()->id,
                    ['role' => ScopedRole::PublicationAdmin->toSlug()]
                );
                break;
            default:
                $role = [
                    'review_coordinator' => ScopedRole::ReviewCoordinator,
                    'reviewer' => ScopedRole::Reviewer,
                    'submitter' => ScopedRole::Submitter,
                ][$actor];
                $user = User::factory()->create();
                $submission->users()->attach($user->id, ['role' => $role->toSlug()]);
        }
        $this->actingAs($user);

        return [$submission, $user];
    }

    /**
     * @return array<string, array{0: string, 1: int, 2: bool}>
     */
    public static function changeStatusActorProvider(): array
    {
        return [
            'app admin' => ['app_admin', Submission::UNDER_REVIEW, true],
            'publication admin' => ['publication_admin', Submission::UNDER_REVIEW, true],
            'editor' => ['editor', Submission::UNDER_REVIEW, true],
            'review coordinator' => ['review_coordinator', Submission::UNDER_REVIEW, true],
            'admin of another publication' => ['foreign_publication_admin', Submission::UNDER_REVIEW, false],
            'reviewer' => ['reviewer', Submission::UNDER_REVIEW, false],
            // The submitter's draft-only UpdateStatus grant is how the client
            // currently sends a draft in (changeSubmissionStatus → INITIALLY_SUBMITTED).
            'submitter on a draft' => ['submitter', Submission::DRAFT, true],
            'submitter after draft' => ['submitter', Submission::INITIALLY_SUBMITTED, false],
        ];
    }

    #[DataProvider('changeStatusActorProvider')]
    public function testChangeSubmissionStatusByActor(string $actor, int $status, bool $allowed): void
    {
        [$submission] = $this->actAs($actor, $status);

        $response = $this->graphQL(
            'mutation ($id: ID!, $status: SubmissionStatus!) {
                changeSubmissionStatus(input: { id: $id, status: $status }) { id status }
            }',
            ['id' => $submission->id, 'status' => 'INITIALLY_SUBMITTED']
        );

        if ($allowed) {
            $response->assertJsonPath('data.changeSubmissionStatus.status', 'INITIALLY_SUBMITTED');
            $this->assertSame(Submission::INITIALLY_SUBMITTED, $submission->fresh()->status);
        } else {
            $response->assertJsonPath('errors.0.message', 'UNAUTHORIZED');
            $this->assertSame($status, $submission->fresh()->status);
        }
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function submissionStatusProvider(): array
    {
        $statuses = [
            'INITIALLY_SUBMITTED', 'RESUBMISSION_REQUESTED', 'RESUBMITTED', 'AWAITING_REVIEW',
            'REJECTED', 'ACCEPTED_AS_FINAL', 'EXPIRED', 'UNDER_REVIEW', 'AWAITING_DECISION',
            'REVISION_REQUESTED', 'ARCHIVED', 'DELETED',
        ];

        return array_combine($statuses, array_map(fn(string $s) => [$s], $statuses));
    }

    #[DataProvider('submissionStatusProvider')]
    public function testChangeSubmissionStatusAcceptsEveryStatus(string $status): void
    {
        [$submission] = $this->actAs('editor', Submission::UNDER_REVIEW);

        $response = $this->graphQL(
            'mutation ($id: ID!, $status: SubmissionStatus!) {
                changeSubmissionStatus(input: { id: $id, status: $status }) { id status }
            }',
            ['id' => $submission->id, 'status' => $status]
        );

        $response->assertJsonPath('data.changeSubmissionStatus.status', $status);
    }

    public function testChangeSubmissionStatusSetsUpdatedBy(): void
    {
        [$submission, $editor] = $this->actAs('editor', Submission::UNDER_REVIEW);

        $response = $this->graphQL(
            'mutation ($id: ID!) {
                changeSubmissionStatus(input: { id: $id, status: AWAITING_DECISION }) {
                    created_by { id }
                    updated_by { id }
                }
            }',
            ['id' => $submission->id]
        );

        $response->assertJsonPath('data.changeSubmissionStatus.created_by.id', (string)$submission->created_by);
        $response->assertJsonPath('data.changeSubmissionStatus.updated_by.id', (string)$editor->id);
    }

    public function testUpdateSubmissionContentSetsUpdatedBy(): void
    {
        [$submission, $submitter] = $this->actAs('submitter', Submission::DRAFT);

        $response = $this->graphQL(
            'mutation ($id: ID!) {
                updateSubmissionContent(input: { id: $id, title: "Renamed" }) {
                    updated_by { id }
                }
            }',
            ['id' => $submission->id]
        );

        $response->assertJsonPath('data.updateSubmissionContent.updated_by.id', (string)$submitter->id);
    }

    /**
     * Roster mutations: [mutation, input field] by role.
     *
     * @return array<string, array{0: string, 1: string, 2: string, 3: bool}>
     */
    public static function rosterActorProvider(): array
    {
        $rosters = [
            'submitters' => ['updateSubmissionSubmitters', 'submitters'],
            'reviewers' => ['updateSubmissionReviewers', 'reviewers'],
            'review coordinators' => ['updateSubmissionReviewCoordinators', 'review_coordinators'],
        ];
        $matrix = [
            'app admin' => ['app_admin', ['submitters' => true, 'reviewers' => true, 'review coordinators' => true]],
            'publication admin' => [
                'publication_admin',
                ['submitters' => true, 'reviewers' => true, 'review coordinators' => true],
            ],
            'admin of another publication' => [
                'foreign_publication_admin',
                ['submitters' => false, 'reviewers' => false, 'review coordinators' => false],
            ],
            'review coordinator' => [
                'review_coordinator',
                ['submitters' => true, 'reviewers' => true, 'review coordinators' => false],
            ],
            'reviewer' => ['reviewer', ['submitters' => false, 'reviewers' => false, 'review coordinators' => false]],
        ];

        $cases = [];
        foreach ($matrix as $actorLabel => [$actor, $allowed]) {
            foreach ($rosters as $rosterLabel => [$mutation, $field]) {
                $cases["$actorLabel / $rosterLabel"] = [$actor, $mutation, $field, $allowed[$rosterLabel]];
            }
        }

        return $cases;
    }

    #[DataProvider('rosterActorProvider')]
    public function testRosterMutationsByActor(string $actor, string $mutation, string $field, bool $allowed): void
    {
        [$submission] = $this->actAs($actor);
        $target = User::factory()->create();

        $response = $this->graphQL(
            'mutation ($id: ID!, $connect: [ID!]) {
                ' . $mutation . '(input: { id: $id, ' . $field . ': { connect: $connect } }) {
                    id
                    ' . $field . ' { id }
                }
            }',
            ['id' => $submission->id, 'connect' => [$target->id]]
        );

        if ($allowed) {
            $ids = array_column($response->json("data.$mutation.$field"), 'id');
            $this->assertContains((string)$target->id, $ids);
        } else {
            $response->assertJsonPath('errors.0.message', 'UNAUTHORIZED');
        }
    }

    /**
     * Title is the author's content: only the submitter (on a draft) or the
     * app admin may edit it; editorial roles may not.
     *
     * @return array<string, array{0: string, 1: bool}>
     */
    public static function titleActorProvider(): array
    {
        return [
            'app admin' => ['app_admin', true],
            'submitter' => ['submitter', true],
            'review coordinator' => ['review_coordinator', false],
            'editor' => ['editor', false],
            'publication admin' => ['publication_admin', false],
        ];
    }

    #[DataProvider('titleActorProvider')]
    public function testUpdateSubmissionContentTitleByActor(string $actor, bool $allowed): void
    {
        [$submission] = $this->actAs($actor, Submission::DRAFT);

        $response = $this->graphQL(
            'mutation ($id: ID!, $title: String!) {
                updateSubmissionContent(input: { id: $id, title: $title }) { id title }
            }',
            ['id' => $submission->id, 'title' => 'A revised title']
        );

        if ($allowed) {
            $response->assertJsonPath('data.updateSubmissionContent.title', 'A revised title');
        } else {
            $response->assertJsonPath('errors.0.message', 'UNAUTHORIZED');
        }
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function invalidTitleProvider(): array
    {
        return [
            'empty' => [''],
            'too long' => [str_repeat('1234567890', 52) . 'x'],
        ];
    }

    #[DataProvider('invalidTitleProvider')]
    public function testUpdateSubmissionContentRejectsInvalidTitle(string $title): void
    {
        [$submission] = $this->actAs('submitter', Submission::DRAFT);
        $original = $submission->title;

        // Nullable variable: the request middleware turns "" into null, so an
        // empty title arrives as a missing one and must still fail validation.
        $response = $this->graphQL(
            'mutation ($id: ID!, $title: String) {
                updateSubmissionContent(input: { id: $id, title: $title }) { id title }
            }',
            ['id' => $submission->id, 'title' => $title]
        );

        $this->assertStringContainsStringIgnoringCase('validation', $response->json('errors.0.message'));
        $this->assertSame($original, $submission->fresh()->title);
    }

    // ---- Comment payload round-trips and reply-shape validation -------------

    public function testCreateInlineCommentRoundTripsStyleCriteriaAndRange(): void
    {
        [$submission] = $this->actAs('reviewer');
        $criteria1 = $this->createStyleCriteria($submission->publication->id);
        $criteria2 = $this->createStyleCriteria($submission->publication->id);

        $response = $this->graphQL(
            'mutation ($id: ID!, $criteria: [ID!]) {
                createInlineComment(input: {
                    submission_id: $id
                    content: "Test Content"
                    style_criteria: $criteria
                    from: 100
                    to: 110
                    parent_id: null
                    reply_to_id: null
                }) {
                    inline_comments { content style_criteria { name icon } read_at from to }
                }
            }',
            ['id' => $submission->id, 'criteria' => [$criteria1->id, $criteria2->id]]
        );

        $response->assertJsonPath('data.createInlineComment.inline_comments', [[
            'content' => 'Test Content',
            'style_criteria' => [
                ['name' => $criteria1->name, 'icon' => $criteria1->icon],
                ['name' => $criteria2->name, 'icon' => $criteria2->icon],
            ],
            'read_at' => null,
            'from' => 100,
            'to' => 110,
        ]]);
    }

    public function testUpdateInlineCommentEditsStyleCriteriaAndRange(): void
    {
        [$submission] = $this->actAs('reviewer');
        $comment = $submission->inlineComments()->create([
            'content' => 'Original',
            'style_criteria' => [],
            'from' => 1,
            'to' => 5,
        ]);
        $criteria1 = $this->createStyleCriteria($submission->publication->id);
        $criteria2 = $this->createStyleCriteria($submission->publication->id);

        $response = $this->graphQL(
            'mutation ($id: ID!, $comment: ID!, $criteria: [ID!]) {
                updateInlineComment(input: {
                    submission_id: $id
                    comment_id: $comment
                    content: "Test Content"
                    style_criteria: $criteria
                    from: 120
                    to: 130
                }) {
                    inline_comments { id content style_criteria { name icon } from to }
                }
            }',
            ['id' => $submission->id, 'comment' => $comment->id, 'criteria' => [$criteria1->id, $criteria2->id]]
        );

        $response->assertJsonPath('data.updateInlineComment.inline_comments', [[
            'id' => (string)$comment->id,
            'content' => 'Test Content',
            'style_criteria' => [
                ['name' => $criteria1->name, 'icon' => $criteria1->icon],
                ['name' => $criteria2->name, 'icon' => $criteria2->icon],
            ],
            'from' => 120,
            'to' => 130,
        ]]);
    }

    /**
     * Reply shapes. A top-level comment names neither id; a reply names both.
     * `true` marks an id that should point at the parent; `null` is explicit null.
     *
     * @return array<string, array{0: bool, 1: array<string, bool|null>}>
     */
    public static function replyShapeProvider(): array
    {
        return [
            'both set' => [true, ['parent_id' => true, 'reply_to_id' => true]],
            'parent only' => [false, ['parent_id' => true]],
            'parent null, reply_to missing' => [true, ['parent_id' => null]],
            'parent null, reply_to set' => [false, ['parent_id' => null, 'reply_to_id' => true]],
            'parent set, reply_to null' => [false, ['parent_id' => true, 'reply_to_id' => null]],
            'parent missing, reply_to null' => [true, ['reply_to_id' => null]],
            'reply_to only' => [false, ['reply_to_id' => true]],
        ];
    }

    #[DataProvider('replyShapeProvider')]
    public function testCreateInlineCommentReplyShape(bool $valid, array $ids): void
    {
        $this->assertReplyShape('createInlineComment', 'inlineComments', $valid, $ids);
    }

    #[DataProvider('replyShapeProvider')]
    public function testCreateOverallCommentReplyShape(bool $valid, array $ids): void
    {
        $this->assertReplyShape('createOverallComment', 'overallComments', $valid, $ids);
    }

    /**
     * @param string $mutation
     * @param string $relation
     * @param bool $valid
     * @param array<string, bool|null> $ids
     * @return void
     */
    private function assertReplyShape(string $mutation, string $relation, bool $valid, array $ids): void
    {
        [$submission] = $this->actAs('reviewer');
        $parentAttrs = ['content' => 'Parent'];
        if ($relation === 'inlineComments') {
            $parentAttrs['style_criteria'] = [];
        }
        $parent = $submission->{$relation}()->create($parentAttrs);

        $fields = '';
        foreach ($ids as $key => $value) {
            $fields .= $key . ': ' . ($value === null ? 'null' : $parent->id) . "\n";
        }

        $response = $this->graphQL(
            'mutation ($id: ID!) {
                ' . $mutation . '(input: { submission_id: $id, content: "Reply" ' . $fields . ' }) { id }
            }',
            ['id' => $submission->id]
        );

        if ($valid) {
            $response->assertJsonPath("data.$mutation.id", (string)$submission->id);
            $this->assertSame(2, $submission->{$relation . 'WithReplies'}()->count());
        } else {
            $response->assertGraphQLValidationError('input.submission_id', 'Invalid reply.');
            $this->assertSame(1, $submission->{$relation . 'WithReplies'}()->count());
        }
    }
}
