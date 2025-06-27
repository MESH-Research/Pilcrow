<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\InlineComment;
use App\Models\OverallComment;
use App\Models\StyleCriteria;
use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionCommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return Submission
     */
    private function createSubmission()
    {
        $user = User::factory()->create();

        return Submission::factory()
            ->hasAttached($user, [], 'submitters')
            ->create();
    }

    /**
     * @param int $id
     * @return StyleCriteria
     */
    private function createStyleCriteria($id)
    {
        $criteria = StyleCriteria::factory()
            ->create([
                'name' => 'PHPUnit Criteria',
                'publication_id' => $id,
                'description' => 'This is a test style criteria created by PHPUnit',
                'icon' => 'php',
            ]);

        return $criteria;
    }

    /**
     * @param int $count
     * @return Submission
     */
    private function createSubmissionWithInlineComment($count = 1)
    {
        $user = User::factory()->create();
        $submission = $this->createSubmission();
        $style_criteria = $this->createStyleCriteria($submission->publication->id);
        InlineComment::factory()->count($count)->create([
            'submission_id' => $submission->id,
            'content' => 'This is some content for an inline comment created by PHPUnit.',
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'style_criteria' => [$style_criteria->toArray()],
        ]);

        return $submission;
    }

    /**
     * @param int $count
     * @return Submission
     */
    private function createSubmissionWithOverallComment($count = 1)
    {
        $user = User::factory()->create();
        $submission = $this->createSubmission();
        OverallComment::factory()->count($count)->create([
            'submission_id' => $submission->id,
            'content' => 'This is some content for an overall comment created by PHPUnit.',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return $submission;
    }

    public function testInlineCommentsAreNotRetrievedForASubmissionThatHasNone()
    {
        $submission = $this->createSubmission();
        $this->assertEmpty($submission->inlineComments);
    }

    public function testOverallCommentsAreNotRetrievedForASubmissionThatHasNone()
    {
        $submission = $this->createSubmission();
        $this->assertEmpty($submission->overallComments);
    }

    public function testInlineCommentsCanBeRetrievedBySubmission()
    {
        $submission = $this->createSubmissionWithInlineComment();
        $this->assertEquals(1, $submission->inlineComments->count());
    }

    public function testOverallCommentsCanBeRetrievedBySubmission()
    {
        $submission = $this->createSubmissionWithOverallComment();
        $this->assertEquals(1, $submission->overallComments->count());
    }

    public function testInlineCommentsStartUnread()
    {
        $this->actingAs(User::factory()->create());
        $submission = $this->createSubmissionWithInlineComment();
        $this->assertEquals(null, $submission->inlineComments->first()->read);
    }

    public function testCanSetInlineCommentAsRead()
    {
        $this->actingAs(User::factory()->create());
        $submission = $this->createSubmissionWithInlineComment();
        $comment = $submission->inlineComments->first();
        $comment->read_at = true;
        $submission->save();
        $this->assertInstanceOf(Carbon::class, $comment->readAt);
    }

    public function testInlineCommentThrowsOnReadSetWithoutUser()
    {
        $this->expectException(\Exception::class);
        $submission = $this->createSubmissionWithInlineComment();
        $submission->inlineComments->first()->readAt = true;
    }

    public function testOverallCommentsStartUnread()
    {
        $this->actingAs(User::factory()->create());
        $submission = $this->createSubmissionWithOverallComment();
        $this->assertEquals(null, $submission->overallComments->first()->readAt);
    }

    public function testCanSetOverallCommentAsRead()
    {
        $this->actingAs(User::factory()->create());
        $submission = $this->createSubmissionWithOverallComment();
        $submission->overallComments->first()->readAt = true;
        $submission->save();
        $this->assertInstanceOf(Carbon::class, $submission->overallComments->first()->readAt);
    }

    public function testOverallCommentThrowsOnReadSetWithoutUser()
    {
        $this->expectException(\Exception::class);
        $submission = $this->createSubmissionWithOverallComment();
        $submission->overallComments->first()->readAt = true;
    }
}
