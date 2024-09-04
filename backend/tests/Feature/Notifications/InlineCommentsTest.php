<?php
declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\InlineComment;
use App\Models\User;
use Tests\TestCase;
use Tests\TestFactory;

class InlineCommentsTest extends TestCase
{
    use TestFactory;

    private function createSubmissionWithInlineCommentThread()
    {
        $submission = $this->createSubmissionWithAllRoles();

        // Create comment participants
        $commentor = User::factory()->create();
        $commentor_reply = User::factory()->create();
        $commentor_reply_to_reply = User::factory()->create();
        $commentor_elsewhere = User::factory()->create();

        // Assign comment participants as submission reviewers
        $submission->reviewers()->attach([
            $commentor->id,
            $commentor_reply->id,
            $commentor_reply_to_reply->id,
            $commentor_elsewhere->id,
        ]);

        // Make comments
        $comment_parent = InlineComment::factory()->create([
            'submission_id' => $submission->id,
            'content' => 'This is some content for an inline comment created by PHPUnit.',
            'created_by' => $commentor->id,
            'updated_by' => $commentor->id,
            'style_criteria' => [],
            'parent_id' => null,
            'reply_to_id' => null,
        ]);
        $comment_reply = InlineComment::factory()->create([
            'submission_id' => $submission->id,
            'content' => 'This is some content for an inline comment reply created by PHPUnit.',
            'created_by' => $commentor_reply->id,
            'updated_by' => $commentor_reply->id,
            'style_criteria' => [],
            'parent_id' => $comment_parent->id,
            'reply_to_id' => $comment_parent->id,
        ]);
        InlineComment::factory()->create([
            'submission_id' => $submission->id,
            'content' => 'This is some content for an inline comment reply to a reply created by PHPUnit.',
            'created_by' => $commentor_reply_to_reply->id,
            'updated_by' => $commentor_reply_to_reply->id,
            'style_criteria' => [],
            'parent_id' => $comment_parent->id,
            'reply_to_id' => $comment_reply->id,
        ]);
        InlineComment::factory()->create([
            'submission_id' => $submission->id,
            'content' => 'This is some content for an inline comment created by PHPUnit.',
            'created_by' => $commentor_elsewhere->id,
            'updated_by' => $commentor_elsewhere->id,
            'style_criteria' => [],
            'parent_id' => null,
            'reply_to_id' => null,
        ]);

        return $submission;
    }

    /**
     * @return void
     */
    public function testUsersReceiveNotificationsForNewInlineComments()
    {
        $submission = $this->createSubmissionWithInlineCommentThread();
        $comments = $submission->inlineCommentsWithReplies();
        $this->assertEquals(4, $comments->count());

        // Submitter
        $submitter = $submission->submitters()->first();
        $this->assertEquals(2, $this->getInlineCommentNotificationCount($submitter));
        $this->assertEquals(2, $this->getInlineCommentReplyNotificationCount($submitter));

        // Uninvolved First Reviewer
        $reviewer1 = $submission->reviewers()->first();
        $this->assertEquals(0, $this->getInlineCommentNotificationCount($reviewer1));
        $this->assertEquals(0, $this->getInlineCommentReplyNotificationCount($reviewer1));

        // Inline Commentor
        $reviewer2 = $submission->reviewers()->get()->slice(1,1)->first();
        $this->assertEquals(0, $this->getInlineCommentNotificationCount($reviewer2));
        $this->assertEquals(2, $this->getInlineCommentReplyNotificationCount($reviewer2));

        // Inline Comment Replier
        $reviewer3 = $submission->reviewers()->get()->slice(2,1)->first();
        $this->assertEquals(0, $this->getInlineCommentNotificationCount($reviewer3));
        $this->assertEquals(1, $this->getInlineCommentReplyNotificationCount($reviewer3));

        // Inline Comment Reply Replier
        $reviewer4 = $submission->reviewers()->get()->slice(3,1)->first();
        $this->assertEquals(0, $this->getInlineCommentNotificationCount($reviewer4));
        $this->assertEquals(0, $this->getInlineCommentReplyNotificationCount($reviewer4));

        // Separate Inline Commentor
        $reviewer5 = $submission->reviewers()->first();
        $this->assertEquals(0, $this->getInlineCommentNotificationCount($reviewer5));
        $this->assertEquals(0, $this->getInlineCommentReplyNotificationCount($reviewer5));
    }

    /**
     * @param User $user
     * @return int
     */
    private function getInlineCommentNotificationCount($user)
    {
        $type = 'App\Notifications\InlineCommentAdded';
        return $user->notifications()->where('type', $type)->get()->count();
    }

    /**
     * @param User $user
     * @return int
     */
    private function getInlineCommentReplyNotificationCount($user)
    {
        $type = 'App\Notifications\InlineCommentReplyAdded';
        return $user->notifications()->where('type', $type)->get()->count();
    }

}
