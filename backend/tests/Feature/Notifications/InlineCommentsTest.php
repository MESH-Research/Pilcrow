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
        $commenter = User::factory()->create();
        $commenter_reply = User::factory()->create();
        $commenter_reply_to_reply = User::factory()->create();
        $commenter_elsewhere = User::factory()->create();

        // Assign comment participants as submission reviewers
        $submission->reviewers()->attach([
            $commenter->id,
            $commenter_reply->id,
            $commenter_reply_to_reply->id,
            $commenter_elsewhere->id,
        ]);

        // Make comments
        InlineComment::factory()->create([
            'submission_id' => $submission->id,
            'content' => 'This is some content for an inline comment created by PHPUnit.',
            'created_by' => $commenter_elsewhere->id,
            'updated_by' => $commenter_elsewhere->id,
            'style_criteria' => [],
            'parent_id' => null,
            'reply_to_id' => null,
        ]);
        $comment_parent = InlineComment::factory()->create([
            'submission_id' => $submission->id,
            'content' => 'This is some content for an inline comment created by PHPUnit.',
            'created_by' => $commenter->id,
            'updated_by' => $commenter->id,
            'style_criteria' => [],
            'parent_id' => null,
            'reply_to_id' => null,
        ]);
        $comment_reply = InlineComment::factory()->create([
            'submission_id' => $submission->id,
            'content' => 'This is some content for an inline comment reply created by PHPUnit.',
            'created_by' => $commenter_reply->id,
            'updated_by' => $commenter_reply->id,
            'style_criteria' => [],
            'parent_id' => $comment_parent->id,
            'reply_to_id' => $comment_parent->id,
        ]);
        InlineComment::factory()->create([
            'submission_id' => $submission->id,
            'content' => 'This is some content for an inline comment reply to a reply created by PHPUnit.',
            'created_by' => $commenter_reply_to_reply->id,
            'updated_by' => $commenter_reply_to_reply->id,
            'style_criteria' => [],
            'parent_id' => $comment_parent->id,
            'reply_to_id' => $comment_reply->id,
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
        // Gets all 4 notifications
        $submitter = $submission->submitters()->first();
        $this->assertEquals(2, $this->getInlineCommentNotificationCount($submitter));
        $this->assertEquals(2, $this->getInlineCommentReplyNotificationCount($submitter));

        // Uninvolved First Reviewer
        // Gets 0 notifications
        $reviewer1 = $submission->reviewers()->first();
        $this->assertEquals(0, $this->getInlineCommentNotificationCount($reviewer1));
        $this->assertEquals(0, $this->getInlineCommentReplyNotificationCount($reviewer1));

        // Inline Commenter
        // Gets 2 notifications for all replies
        $reviewer2 = $submission->reviewers()->get()->slice(1, 1)->first();
        $this->assertEquals(0, $this->getInlineCommentNotificationCount($reviewer2));
        $this->assertEquals(2, $this->getInlineCommentReplyNotificationCount($reviewer2));

        // Inline Comment Replier
        // Gets 1 notification for reply to reply
        $reviewer3 = $submission->reviewers()->get()->slice(2, 1)->first();
        $this->assertEquals(0, $this->getInlineCommentNotificationCount($reviewer3));
        $this->assertEquals(1, $this->getInlineCommentReplyNotificationCount($reviewer3));

        // Inline Comment Reply Replier
        // Gets 0 notifications
        $reviewer4 = $submission->reviewers()->get()->slice(3, 1)->first();
        $this->assertEquals(0, $this->getInlineCommentNotificationCount($reviewer4));
        $this->assertEquals(0, $this->getInlineCommentReplyNotificationCount($reviewer4));

        // Separate Inline Commenter
        // Gets 0 notifications
        $reviewer5 = $submission->reviewers()->first();
        $this->assertEquals(0, $this->getInlineCommentNotificationCount($reviewer5));
        $this->assertEquals(0, $this->getInlineCommentReplyNotificationCount($reviewer5));

        // Coordinator
        // Gets all 4 notifications
        $coordinator = $submission->reviewCoordinators()->first();
        $this->assertEquals(2, $this->getInlineCommentNotificationCount($coordinator));
        $this->assertEquals(2, $this->getInlineCommentReplyNotificationCount($coordinator));

        // Editor
        // Gets 0 notifications
        $editor = $submission->publication->editors()->first();
        $this->assertEquals(0, $this->getInlineCommentNotificationCount($editor));
        $this->assertEquals(0, $this->getInlineCommentReplyNotificationCount($editor));

        // Publication Admins
        // Gets 0 notifications
        $admin = $submission->publication->publicationAdmins()->first();
        $this->assertEquals(0, $this->getInlineCommentNotificationCount($admin));
        $this->assertEquals(0, $this->getInlineCommentReplyNotificationCount($admin));
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
