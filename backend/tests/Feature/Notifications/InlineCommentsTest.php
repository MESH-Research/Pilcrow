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
            'content' => 'This is some content for an inline comment created by PHPUnit.',
            'created_by' => $commentor_reply->id,
            'updated_by' => $commentor_reply->id,
            'style_criteria' => [],
            'parent_id' => $comment_parent->id,
            'reply_to_id' => $comment_parent->id,
        ]);
        InlineComment::factory()->create([
            'submission_id' => $submission->id,
            'content' => 'This is some content for an inline comment created by PHPUnit.',
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
        $submission->submitters()->first()->notifications->map(function ($notification, int $key) use ($submission) {
            switch ($key) {
                case 0:
                    $this->assertInlineComment($notification, $submission);
                    break;
                case 1:
                    $this->assertInlineCommentReply($notification, $submission);
                    break;
                case 2:
                    $this->assertInlineCommentReply($notification, $submission);
                    break;
                case 3:
                    $this->assertInlineComment($notification, $submission);
                    break;
            }
        });
        $submission->reviewers()->first()->notifications->map(function ($notification, int $key) use ($submission) {
            switch ($key) {
                case 0: // Reply to inline comment
                    $this->assertInlineCommentReply($notification, $submission);
                    break;
                case 1: // Reply to reply of inline comment
                    $this->assertInlineCommentReply($notification, $submission);
                    break;
            }
        });
        $submission->reviewCoordinators()->first()->notifications->map(function ($notification, int $key) use ($submission) {
            switch ($key) {
                case 0:
                    $this->assertInlineComment($notification, $submission);
                    break;
                case 1:
                    $this->assertInlineCommentReply($notification, $submission);
                    break;
                case 2:
                    $this->assertInlineCommentReply($notification, $submission);
                    break;
                case 3:
                    $this->assertInlineComment($notification, $submission);
                    break;
            }
        });
    }

    /**
     * @return void
     */
    private function assertInlineComment($notification, $submission)
    {
        try {
            $this->assertEquals("App\Notifications\InlineCommentAdded", $notification->type);
            $this->assertEquals($notification->data['type'], 'submission.inline_comment.added');
            $this->assertEquals($notification->data['submission']['id'], $submission->id);
        } catch (\Exception $e) {
            print_r(User::where('id', $notification->notifiable_id)->first()->toArray());
            print_r('Should be an inline comment');
            print_r($notification->toArray());
        }
    }

    /**
     * @return void
     */
    private function assertInlineCommentReply($notification, $submission)
    {
        try {
            $this->assertEquals("App\Notifications\InlineCommentReplyAdded", $notification->type);
            $this->assertEquals($notification->data['type'], 'submission.inline_comment_reply.added');
            $this->assertEquals($notification->data['submission']['id'], $submission->id);
        } catch (\Exception $e) {
            print_r(User::where('id', $notification->notifiable_id)->first()->toArray());
            print_r('Should be a reply');
            print_r($notification->toArray());
        }
    }

}
