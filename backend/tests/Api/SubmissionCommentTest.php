<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\InlineComment;
use App\Models\OverallComment;
use App\Models\StyleCriteria;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\ApiTestCase;

class SubmissionCommentTest extends ApiTestCase
{
    use RefreshDatabase;

    /**
     * @param int $status (default: Submission::UNDER_REVIEW)
     * @return Submission
     */
    private function createSubmission($status = Submission::UNDER_REVIEW)
    {
        $user = User::factory()->create();

        return Submission::factory()
            ->hasAttached($user, [], 'submitters')
            ->create(['status' => $status]);
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
     * @param User|null $user
     * @return Submission
     */
    private function createSubmissionWithInlineComment($count = 1, $user = null)
    {
        if ($user === null) {
            $user = User::factory()->create();
        }
        $submission = $this->createSubmission();
        $style_criteria = $this->createStyleCriteria($submission->publication->id);
        InlineComment::factory()->count($count)->create([
            'submission_id' => $submission->id,
            'content' => 'This is some content for an inline comment created by PHPUnit.',
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'style_criteria' => [$style_criteria],
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
    public static function commentCreationProvider(): array
    {
        return [
            'parent_id missing and reply_to_id missing' => [true, ''],
            'parent_id null and reply_to_id null' => [true, 'parent_id: null, reply_to_id: null'],
        ];
    }

    /**
     * @dataProvider commentCreationProvider
     * @param bool $is_valid Expected validity of test case
     * @param string $fragment GraphQL fragment to include in the mutation
     * @return void
     */
    public function testCreateInlineComment(bool $is_valid, string $fragment)
    {
        $this->beAppAdmin();
        $submission = $this->createSubmission();
        $criteria_1 = $this->createStyleCriteria($submission->publication->id);
        $criteria_2 = $this->createStyleCriteria($submission->publication->id);
        $response = $this->graphQL(
            'mutation AddInlineComment($submission_id: ID!) {
                updateSubmission(input: {
                    id: $submission_id
                    inline_comments: {
                        create: [{
                            content: "Hello World"
                            style_criteria: [' . $criteria_1->id . ', ' . $criteria_2->id . ']
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
                                'name' => $criteria_1->name,
                                'icon' => $criteria_1->icon,
                            ],
                            '1' => [
                                'name' => $criteria_2->name,
                                'icon' => $criteria_2->icon,
                            ],
                        ],
                        'from' => 100,
                        'to' => 110,
                    ],
                ],
            ],
        ];
        if ($is_valid) {
            $response->assertJsonPath('data', $expected);
        } else {
            $this->assertStringStartsWith('Validation failed', $response->json('errors.0.message'));
        }
    }

    /**
     * @return array
     */
    public static function commentReplyCreationProvider(): array
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
     * @param array $args Arguments to include in the GraphQL mutation
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
                            parent_id
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
                            'parent_id' => (string)$inline_comment->id,
                            'reply_to_id' => (string)$inline_comment->id,
                        ],
                    ],
                ],
            ]);
        } else {
            $this->assertStringStartsWith('Validation failed', $response->json('errors.0.message'));
        }
    }

    /**
     * @dataProvider commentCreationProvider
     * @param bool $is_valid Expected validity of test case
     * @param string $fragment GraphQL fragment to include in the mutation
     * @return void
     */
    public function testCreateOverallComment(bool $is_valid, string $fragment): void
    {
        $this->beAppAdmin();
        $submission = $this->createSubmission();

        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation CreateOverallComment($submissionId: ID!) {
                updateSubmission(input: {
                    id: $submissionId
                    overall_comments: {
                        create: [
                            {
                                content: "New Overall Comment"
                                ' . $fragment . '
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
        if ($is_valid) {
            $response->assertJsonPath('data.updateSubmission.overall_comments', [
                [
                    'content' => 'New Overall Comment',
                ],
            ]);
        } else {
            $this->assertStringStartsWith('Validation failed', $response->json('errors.0.message'));
        }
    }

    /**
     * @dataProvider commentReplyCreationProvider
     * @param bool $is_valid Expected validity of test case
     * @param array $args Arguments to include in the GraphQL mutation
     * @return void
     */
    public function testCreateOverallCommentReply(bool $is_valid, array $args): void
    {
        $this->beAppAdmin();
        $submission = $this->createSubmissionWithOverallComment();
        $overall_comment = $submission->overallComments()->first();

        //phpcs:disable
        $parentId = $overall_comment->id;
        $replyToId = $overall_comment->id;
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
            'mutation CreateOverallCommentReply($submissionId: ID! ' . implode(' ', $arguments) . ') {
                updateSubmission(input: {
                    id: $submissionId
                    overall_comments: {
                        create: [
                            {
                                content: "New Overall Comment Reply"
                                ' . implode("\n", $fragment) . '
                            }
                        ]
                    }
                }) {
                    overall_comments {
                        replies {
                            content
                            parent_id
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
            $response->assertJsonPath('data.updateSubmission.overall_comments', [
                [
                    'replies' => [
                        [
                            'content' => 'New Overall Comment Reply',
                            'parent_id' => (string)$overall_comment->id,
                            'reply_to_id' => (string)$overall_comment->id,
                        ],
                    ],
                ],
            ]);
        } else {
            $this->assertStringStartsWith('Validation failed', $response->json('errors.0.message'));
        }
    }

    /**
     * @return void
     */
    public function testUpdateInlineComment()
    {
        $admin = $this->beAppAdmin();
        $submission = $this->createSubmissionWithInlineComment(1, $admin);
        $inline_comment = $submission->inlineComments->first();
        $criteria_1 = $this->createStyleCriteria($submission->publication->id);
        $criteria_2 = $this->createStyleCriteria($submission->publication->id);
        $response = $this->graphQL(
            'mutation UpdateInlineComment($submissionId: ID! $commentId: ID!) {
                updateSubmission(input: {
                    id: $submissionId
                    inline_comments: {
                        update: [
                            {
                                id: $commentId
                                content: "Hello World Updated"
                                style_criteria: [' . $criteria_1->id . ', ' . $criteria_2->id . ']
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
                                'name' => $criteria_1->name,
                                'icon' => $criteria_1->icon,
                            ],
                            '1' => [
                                'name' => $criteria_2->name,
                                'icon' => $criteria_2->icon,
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

    /**
     * @return void
     */
    public function testSubmissionOutOfReviewRejectsNewInlineComments(): void
    {
        $user = $this->beSubmitter();
        $submission = $user->submissions->first();
        $this->graphQL(
            'mutation AddInlineComment ($submission_id: ID!) {
                updateSubmission(
                    input: {
                        id: $submission_id,
                        inline_comments: {create: [{content:"Hello World", reply_to_id: null, parent_id: null, from: 120, to: 130 }]}
                    }
                ) {
                    id
                }
            }',
            [
                'submission_id' => $submission->id,
            ]
        )
        ->assertGraphQLErrorMessage('Validation failed for the field [updateSubmission].')
        ->assertGraphQLValidationError(
            'input.inline_comments.create.0',
            'The submission is not in a reviewable state.'
        );
    }

    /**
     * @return void
     */
    public function testSubmissionUnderReviewAcceptsNewInlineComments(): void
    {
        $user = $this->beSubmitter();
        $submission = $user->submissions->first();
        $submission->status = Submission::UNDER_REVIEW;
        $submission->save();
        $response = $this->graphQL(
            'mutation AddInlineComment ($submission_id: ID!) {
                updateSubmission(
                    input: {
                        id: $submission_id,
                        inline_comments: {create: [{content:"Hello World", reply_to_id: null, parent_id: null, from: 120, to: 130 }]}
                    }
                ) {
                    id
                    inline_comments {
                        content
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
                'id' => (string)$submission->id,
                'inline_comments' => [
                    [
                        'content' => 'Hello World',
                        'from' => 120,
                        'to' => 130,
                    ],
                ],
            ],
        ];
        $response->assertJsonPath('data', $expected);
    }

    /**
     * @return void
     */
    public function testSubmissionOutOfReviewRejectsNewOverallComments(): void
    {
        $user = $this->beSubmitter();
        $submission = $user->submissions->first();
        $this->graphQL(
            'mutation AddOverallComment ($submission_id: ID!) {
                updateSubmission(
                    input: {
                        id: $submission_id,
                        overall_comments: {create: [{content:"Hello World", reply_to_id: null, parent_id: null }]}
                    }
                ) {
                    id
                }
            }',
            [
                'submission_id' => $submission->id,
            ]
        )
        ->assertGraphQLErrorMessage('Validation failed for the field [updateSubmission].')
        ->assertGraphQLValidationError(
            'input.overall_comments.create.0',
            'The submission is not in a reviewable state.'
        );
    }

    /**
     * @return void
     */
    public function testSubmissionUnderReviewAcceptsNewOverallComments(): void
    {
        $user = $this->beSubmitter();
        $submission = $user->submissions->first();
        $submission->status = Submission::UNDER_REVIEW;
        $submission->save();
        $response = $this->graphQL(
            'mutation AddOverallComment ($submission_id: ID!) {
                updateSubmission(
                    input: {
                        id: $submission_id,
                        overall_comments: {create: [{content:"Hello World", reply_to_id: null, parent_id: null }]}
                    }
                ) {
                    id
                    overall_comments {
                        content
                    }
                }
            }',
            [
                'submission_id' => $submission->id,
            ]
        );
        $expected = [
            'updateSubmission' => [
                'id' => (string)$submission->id,
                'overall_comments' => [
                    [
                        'content' => 'Hello World',
                    ],
                ],
            ],
        ];
        $response->assertJsonPath('data', $expected);
    }

    /**
     * @return void
     */
    public function testUsersCannotModifyTheInlineCommentsOfOthers(): void
    {
        $this->beAppAdmin();
        $submission = $this->createSubmissionWithInlineComment();
        $inline_comment = $submission->inlineComments->first();
        $this->graphQL(
            'mutation UpdateOthersInlineComment ($submission_id: ID! $comment_id: ID!) {
                updateSubmission(
                    input: {
                        id: $submission_id,
                        inline_comments: {
                            update: {
                                id: $comment_id
                                content: "This update should not work."
                            }
                        }
                    }
                ) {
                    id
                }
            }',
            [
                'submission_id' => $submission->id,
                'comment_id' => $inline_comment->id,
            ]
        )
        ->assertGraphQLErrorMessage('UNAUTHORIZED');
    }

    /**
     * @return void
     */
    public function testUsersCannotModifyTheOverallCommentsOfOthers()
    {
        $this->beAppAdmin();
        $submission = $this->createSubmissionWithOverallComment();
        $overall_comment = $submission->overallComments->first();
        $this->graphQL(
            'mutation UpdateOthersOverallComment ($submission_id: ID! $comment_id: ID!) {
                updateSubmission(
                    input: {
                        id: $submission_id,
                        overall_comments: {
                            update: {
                                id: $comment_id
                                content: "This update should not work."
                            }
                        }
                    }
                ) {
                    id
                }
            }',
            [
                'submission_id' => $submission->id,
                'comment_id' => $overall_comment->id,
            ]
        )
        ->assertGraphQLErrorMessage('UNAUTHORIZED');
    }
}
