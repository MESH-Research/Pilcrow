<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class InlineCommentTest extends TestCase
{
    use RefreshDatabase;
    use MakesGraphQLRequests;

    /**
     * @return void
     */
    public function testInlineCommentsCanBeCreatedOnTheGraphqlEndpoint()
    {
        $this->beAppAdmin();
        $submission = Submission::factory()->create();
        $response = $this->graphQL('
            mutation AddInlineComment($submission_id: ID!) {
                addInlineComment(
                    submission_id: $submission_id
                    content: "Hello World"
                    from: 100
                    to: 110
                ) {
                    id
                    content
                    from
                    to
                }
            }',
            [
                'submission_id' => $submission->id,
            ]
        );
        $expected = [
            'addInlineComment' => [
                'id' => (string)$submission->id,
                'content' => 'Hello World',
                'from' => 100,
                'to' => 110,
            ]
        ];
        $response->assertJsonPath('data', $expected);
    }
}
