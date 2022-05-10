<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\InlineComment;
use App\Models\OverallComment;
use App\Models\Publication;
use App\Models\Role;
use App\Models\StyleCriteria;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class SubmissionCommentTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    /**
     * @return Submission
     */
    private function createSubmission()
    {
        $publication = Publication::factory()->create();
        $user = User::factory()->create();

        return Submission::factory()->hasAttached(
            $user,
            [
                'role_id' => Role::SUBMITTER_ROLE_ID,
            ]
        )->create([
            'publication_id' => $publication->id,
        ]);
    }

    /**
     * @param int $id
     * @return StyleCriteria
     */
    private function createStyleCriteria($id)
    {
        return StyleCriteria::factory()
        ->create([
            'name' => 'PHPUnit Criteria',
            'publication_id' => $id,
            'description' => 'This is a test style criteria created by PHPUnit',
            'icon' => 'php',
        ]);
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
            'inline_comment_style_criteria' => $style_criteria->toArray(),
        ]);
        print_r("Inline Comments\r\n");
        print_r($submission->inline_comments->toArray());
        return $submission;
    }

    // /**
    //  * @param int $count
    //  * @return Submission
    //  */
    // private function createSubmissionWithOverallComment($count = 1)
    // {
    //     $user = User::factory()->create();
    //     $submission = $this->createSubmission();
    //     OverallComment::factory()->count($count)->create([
    //         'submission_id' => $submission->id,
    //         'created_by' => $user->id,
    //         'updated_by' => $user->id,
    //     ]);

    //     return $submission;
    // }

    // public function testInlineCommentsAreNotRetrievedForASubmissionThatHasNone()
    // {
    //     $submission = $this->createSubmission();
    //     $this->assertEmpty($submission->inline_comments);
    // }

    // public function testOverallCommentsAreNotRetrievedForASubmissionThatHasNone()
    // {
    //     $submission = $this->createSubmission();
    //     $this->assertEmpty($submission->overall_comments);
    // }

    // public function testInlineCommentsCanBeRetrievedBySubmission()
    // {
    //     $submission = $this->createSubmissionWithInlineComment();
    //     $this->assertEquals(1, $submission->inline_comments->count());
    // }

    // public function testOverallCommentsCanBeRetrievedBySubmission()
    // {
    //     $submission = $this->createSubmissionWithOverallComment();
    //     $this->assertEquals(1, $submission->overall_comments->count());
    // }

    public function testInlineCommentsCanBeRetrievedOnTheGraphqlEndpoint()
    {
        $submission = $this->createSubmissionWithInlineComment(2);
        $submission_query = Submission::get()->first()->publication->styleCriterias->toArray();
        print_r("Style Criteria on the queried submission\r\n");
        print_r($submission_query);
        $criteria_query = StyleCriteria::get()->toArray();
        print_r("Criteria queried separately\r\n");
        print_r($criteria_query);
        $response = $this->graphQL(
            'query GetSubmission($id: ID!) {
                submission (id: $id) {
                    id
                    inline_comments {
                        content
                        inline_comment_style_criteria {
                            name
                            icon
                        }
                    }
                }
            }',
            [ 'id' => $submission->id ]
        );
        print_r("Decoded GraphQL Response\r\n");
        print_r($response->decodeResponseJson());
        // $expected_data = [
        //     'submission' => [
        //         'id' => (string)$submission->id,
        //         'inline_comments' => [
        //             '0' => [
        //                 'content' => 'This is some content for an inline comment created by PHPUnit.',
        //                 'style_criteria' => [
        //                     '0' => [
        //                         'name' => 'PHPUnit Criteria',
        //                         'icon' => 'php',
        //                     ],
        //                 ],
        //             ],
        //             '1' => [
        //                 'content' => 'This is some content for an inline comment created by PHPUnit.',
        //                 'style_criteria' => [
        //                     '0' => [
        //                         'name' => 'PHPUnit Criteria',
        //                         'icon' => 'php',
        //                     ],
        //                 ],
        //             ],
        //         ],
        //     ],
        // ];
        // $response->assertJsonPath('data', $expected_data);
    }

    // public function testOverallCommentsCanBeRetrievedOnTheGraphqlEndpoint()
    // {
    //     return true;
    // }
}
