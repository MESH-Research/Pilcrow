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

    /**
     * @return void
     */
    public function testUsersReceiveNotificationsForInlineComments()
    {
        $submission = $this->createSubmissionWithInlineComment(4);
        $comment = $submission->inlineComments->first();
        $comment2 = $submission->inlineComments->splice(1, 1)->first();
        $reviewer1 = User::factory()->create();
        $reviewer2 = User::factory()->create();
        $reviewer3 = User::factory()->create();
        $submission->reviewers()->attach([$reviewer1->id, $reviewer2->id, $reviewer3->id]);
        InlineComment::factory()->create([
            'submission_id' => $submission->id,
            'content' => 'This is some content for an inline comment created by PHPUnit.',
            'created_by' => $reviewer1->id,
            'updated_by' => $reviewer1->id,
            'style_criteria' => [],
            'parent_id' => $comment->id,
            'reply_to_id' => $comment->id,
        ]);
        InlineComment::factory()->create([
            'submission_id' => $submission->id,
            'content' => 'This is some content for an inline comment created by PHPUnit.',
            'created_by' => $reviewer2->id,
            'updated_by' => $reviewer2->id,
            'style_criteria' => [],
            'parent_id' => $comment2->id,
            'reply_to_id' => $comment2->id,
        ]);
        $c6 = InlineComment::factory()->create([
            'submission_id' => $submission->id,
            'content' => 'This is some content for an inline comment created by PHPUnit.',
            'created_by' => $reviewer3->id,
            'updated_by' => $reviewer3->id,
            'style_criteria' => [],
            'parent_id' => $comment2->id,
            'reply_to_id' => $comment2->id,
        ]);
        InlineComment::factory()->create([
            'submission_id' => $submission->id,
            'content' => 'This is some content for an inline comment created by PHPUnit.',
            'created_by' => $reviewer2->id,
            'updated_by' => $reviewer2->id,
            'style_criteria' => [],
            'parent_id' => $comment2->id,
            'reply_to_id' => $c6->id,
        ]);

        $comments = $submission->inlineCommentsWithReplies;
        $data = $comments->splice(2, 1)->first()->commentors->unique()->toArray();
    }
}
