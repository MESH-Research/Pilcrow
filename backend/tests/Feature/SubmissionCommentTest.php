<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\InlineComment;
use App\Models\OverallComment;
use App\Models\Publication;
use App\Models\StyleCriteria;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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

        return Submission::factory()
            ->hasAttached($user, [], 'submitters')
            ->create([
                'publication_id' => $publication->id,
            ]);
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

    public function testRetrieveInlineComments()
    {
        $this->beAppAdmin();

        $submission = $this->createSubmissionWithInlineComment(2);
        $response = $this->graphQL(
            'query GetSubmission($id: ID!) {
                submission (id: $id) {
                    id
                    inline_comments {
                        created_by {
                            name
                        }
                        updated_by {
                            name
                        }
                        content
                        style_criteria {
                            name
                            icon
                        }
                    }
                }
            }',
            [ 'id' => $submission->id ]
        );
        $expected_data = [
            'submission' => [
                'id' => (string)$submission->id,
                'inline_comments' => [
                    '0' => [
                        'created_by' => [
                            'name' => $submission->inlineComments->first()->createdBy->name,
                        ],
                        'updated_by' => [
                            'name' => $submission->inlineComments->first()->updatedBy->name,
                        ],
                        'content' => 'This is some content for an inline comment created by PHPUnit.',
                        'style_criteria' => [
                            '0' => [
                                'name' => 'PHPUnit Criteria',
                                'icon' => 'php',
                            ],
                        ],
                    ],
                    '1' => [
                        'created_by' => [
                            'name' => $submission->inlineComments->last()->createdBy->name,
                        ],
                        'updated_by' => [
                            'name' => $submission->inlineComments->last()->updatedBy->name,
                        ],
                        'content' => 'This is some content for an inline comment created by PHPUnit.',
                        'style_criteria' => [
                            '0' => [
                                'name' => 'PHPUnit Criteria',
                                'icon' => 'php',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    public function testRetrieveOverallComments()
    {
        $this->beAppAdmin();

        $submission = $this->createSubmissionWithOverallComment(2);
        $response = $this->graphQL(
            'query GetSubmission($id: ID!) {
                submission (id: $id) {
                    id
                    overall_comments {
                        created_by {
                            name
                        }
                        updated_by {
                            name
                        }
                        content
                    }
                }
            }',
            [ 'id' => $submission->id ]
        );
        $expected_data = [
            'submission' => [
                'id' => (string)$submission->id,
                'overall_comments' => [
                    '0' => [
                        'created_by' => [
                            'name' => $submission->overallComments->first()->createdBy->name,
                        ],
                        'updated_by' => [
                            'name' => $submission->overallComments->first()->updatedBy->name,
                        ],
                        'content' => 'This is some content for an overall comment created by PHPUnit.',
                    ],
                    '1' => [
                        'created_by' => [
                            'name' => $submission->overallComments->last()->createdBy->name,
                        ],
                        'updated_by' => [
                            'name' => $submission->overallComments->last()->updatedBy->name,
                        ],
                        'content' => 'This is some content for an overall comment created by PHPUnit.',
                    ],
                ],
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return array
     */
    public function commentCreationProvider(): array
    {
        return [
            'parent_id missing and reply_to_id missing' => [true, ''],
            'parent_id null and reply_to_id null' => [true, 'parent_id: null, reply_to_id: null'],
        ];
    }

    /**
     * @dataProvider commentCreationProvider
     * @param bool $validity Expected validity of test case
     * @param string $fragment GraphQL fragment to include in the mutation
     * @return void
     */
    public function testCreateInlineComment(bool $validity, string $fragment)
    {
        $this->beAppAdmin();
        $submission = $this->createSubmission();
        $response = $this->graphQL(
            'mutation AddInlineComment($submission_id: ID!) {
                updateSubmission(input: {
                    id: $submission_id
                    inline_comments: {
                        create: [{
                            content: "Hello World"
                            style_criteria: [
                                {
                                    name: "Hello"
                                    icon: "hello"
                                }
                                {
                                    name: "World"
                                    icon: "world"
                                }
                            ]
                            ' . $fragment . '
                            from: 100
                            to: 110
                        }]
                    }
                }) {
                 inline_comments {
                    content
                        style_criteria {
                            name
                            icon
                        }
                        from
                        to
                    }
                }
            }',
            [
                'submission_id' => $submission->id,
            ]
        );
        $expected = [
            'updateSubmission' => [
                'inline_comments' => [
                    [
                        'content' => 'Hello World',
                        'style_criteria' => [
                            '0' => [
                                'name' => 'Hello',
                                'icon' => 'hello',
                            ],
                            '1' => [
                                'name' => 'World',
                                'icon' => 'world',
                            ],
                        ],
                        'from' => 100,
                        'to' => 110,
                    ],
                ],
            ],
        ];
        if ($validity) {
            $response->assertJsonPath('data', $expected);
        }
    }

    /**
     * @return array
     */
    public function commentReplyCreationProvider(): array
    {
        return [
            'parent_id with a value and reply_to_id with a value' =>
                [true, ['parent_id' => true, 'reply_to_id' => true]],
            'parent_id with a value and reply_to_id missing' =>
                [false, ['parent_id' => true]],
            'parent_id null and reply_to_id missing' =>
                [false, ['parent_id' => null]],
            'parent_id null and reply_to_id with a value' =>
                [false, ['parent_id' => null, 'reply_to_id' => true]],
            'parent_id with a value and reply_to_id null' =>
                [false, ['parent_id' => true, 'reply_to_id' => null]],
            'parent_id missing and reply_to_id null' =>
                [false, [ 'reply_to_id' => null]],
            'parent_id missing and reply_to_id with a value' =>
                [false, ['reply_to_id' => true]],
        ];
    }

    /**
     * @dataProvider commentReplyCreationProvider
     * @param bool $is_valid Expected validity of test case
     * @param array $args Arguments to be included in the GraphQL mutation
     * @return void
     */
    public function testCreateInlineCommentReply(bool $is_valid, array $args): void
    {
        $this->beAppAdmin();
        $submission = $this->createSubmissionWithInlineComment();
        $inline_comment = $submission->inlineComments()->first();

        //phpcs:disable
        $parentId = $inline_comment->id;
        $replyToId = $inline_comment->id;
        //phpcs:enable

        $arguments = [];
        $fragment = [];
        $variables = ['submissionId' => $submission->id];
        foreach ($args as $input => $variable) {
            $camelName = Str::camel($input);
            $arguments[] = "$$camelName: ID";
            $fragment[] = "$input: $$camelName";
            $variables[$camelName] = $variable ? $$camelName : null;
        }

        $graphQL =
            /** @lang GraphQL */
            'mutation CreateInlineCommentReply($submissionId: ID! ' . implode(' ', $arguments) . ') {
                updateSubmission(input: {
                    id: $submissionId
                    inline_comments: {
                        create: [
                            {
                                content: "New Inline Comment Reply"
                                ' . implode("\n", $fragment) . '
                            }
                        ]
                    }
                }) {
                    inline_comments {
                        replies {
                            content
                            reply_to_id
                        }
                    }
                }
            }';

        $response = $this->graphql(
            $graphQL,
            $variables
        );

        if ($is_valid) {
            $response->assertJsonPath('data.updateSubmission.inline_comments', [
                [
                    'replies' => [
                        [
                            'content' => 'New Inline Comment Reply',
                            'reply_to_id' => (string)$inline_comment->id,
                        ],
                    ],
                ],
            ]);
        } else {
            $response->assertGraphQLErrorCategory('validation');
        }
    }

    public function testCreateOverallComment(): void
    {
        $this->beAppAdmin();
        $submission = $this->createSubmission();

        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation CreateInlineCommentReply($submissionId: ID!) {
                updateSubmission(input: {
                    id: $submissionId
                    overall_comments: {
                        create: [
                            {
                                content: "New Overall Comment"
                            }
                        ]
                    }
                }) {
                    overall_comments {
                        content
                    }
                }
            }',
            [
                    'submissionId' => $submission->id,
            ]
        );
        $response->assertJsonPath('data.updateSubmission.overall_comments', [
            [
                'content' => 'New Overall Comment',
            ],
        ]);
    }

    public function testCreateOverallCommentReply(): void
    {
        $this->beAppAdmin();
        $submission = $this->createSubmissionWithOverallComment();
        $overall_comment = $submission->overallComments()->first();

        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation CreateOverallCommentReply($submissionId: ID! $parentId: ID $replyToId: ID) {
                updateSubmission(input: {
                    id: $submissionId
                    overall_comments: {
                        create: [
                            {
                                content: "New Overall Comment Reply"
                                parent_id: $parentId
                                reply_to_id: $replyToId
                            }
                        ]
                    }
                }) {
                    overall_comments {
                        replies {
                            content
                            reply_to_id
                        }
                    }
                }
            }',
            [
                    'submissionId' => $submission->id,
                    'parentId' => $overall_comment->id,
                    'replyToId' => $overall_comment->id,
            ]
        );

        $response->assertJsonPath('data.updateSubmission.overall_comments', [
            [
                'replies' => [
                    [
                        'content' => 'New Overall Comment Reply',
                        'reply_to_id' => (string)$overall_comment->id,
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return void
     */
    public function testUpdateInlineComment()
    {
        $this->beAppAdmin();
        $submission = $this->createSubmissionWithInlineComment();
        $inline_comment = $submission->inlineComments()->first();
        $response = $this->graphQL(
            'mutation UpdateInlineComment($submissionId: ID! $commentId: ID!) {
                updateSubmission(input: {
                    id: $submissionId
                    inline_comments: {
                        update: [
                            {
                                id: $commentId
                                content: "Hello World Updated"
                                style_criteria: [
                                    {
                                        name: "Hello"
                                        icon: "hello"
                                    }
                                    {
                                        name: "World"
                                        icon: "world"
                                    }
                                ]
                                from: 120
                                to: 130
                            }
                        ]
                    }
                }) {
                    id
                    inline_comments {
                        id
                        content
                        style_criteria {
                            name
                            icon
                        }
                        from
                        to
                    }
                }
            }',
            [
                'commentId' => $inline_comment->id,
                'submissionId' => $submission->id,
            ]
        );
        $expected = [
            'updateSubmission' => [
                'id' => (string)$submission->id,
                'inline_comments' => [
                    [
                        'id' => (string)$inline_comment->id,
                        'content' => 'Hello World Updated',
                        'style_criteria' => [
                            '0' => [
                                'name' => 'Hello',
                                'icon' => 'hello',
                            ],
                            '1' => [
                                'name' => 'World',
                                'icon' => 'world',
                            ],
                        ],
                        'from' => 120,
                        'to' => 130,
                    ],
                ],
            ],
        ];
        $response->assertJsonPath('data', $expected);
    }
}
