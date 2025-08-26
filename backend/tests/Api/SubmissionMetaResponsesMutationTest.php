<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Enums\MetaPromptType;
use App\Models\MetaForm;
use App\Models\MetaPrompt;
use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class SubmissionMetaResponsesMutationTest extends ApiTestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Submission $userSubmission;
    protected Publication $publication;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->publication = Publication::factory()->create();
        $this->userSubmission = Submission::factory()
            ->for($this->publication)
            ->hasAttached($this->user, [], 'submitters')
            ->state(['status' => Submission::DRAFT])
            ->create();
    }

    #[Test]
    public function canSubmitMetaResponses()
    {
        /** @var User $user */
        $metaForm = MetaForm::factory()
            ->for($this->publication)
            ->has(
                MetaPrompt::factory()
                    ->state(
                        new Sequence(
                            ['label' => 'Favorite color', 'type' => MetaPromptType::INPUT, 'required' => true],
                            ['label' => 'Favorite animal', 'type' => MetaPromptType::INPUT]
                        )
                    )
                    ->count(2)
            )
            ->create([
                'name' => 'Test Meta Form',
            ]);

        $this->actingAs($this->user);

        $response = $this->graphQL(
            /** @lang GraphQL */
            '
            mutation SubmissionMetaFormUpdate($input: SubmissionMetaFormUpdate!) {
                submissionMetaFormUpdate(input: $input) {
                    id
                    meta_form {
                        id
                    }

                }
            }
            ',
            [
                'input' => [
                    'meta_form_id' => $metaForm->id,
                    'submission_id' => $this->userSubmission->id,
                    'responses' => [
                        ['meta_prompt_id' => $metaForm->metaPrompts->offsetGet(0)->id, 'response' => 'Input the first'],
                        ['meta_prompt_id' => $metaForm->metaPrompts->offsetGet(1)->id, 'response' => 'Another test response'],
                    ],
                ],
            ]
        );
        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has(
                'data.submissionMetaFormUpdate',
                fn($json) =>
                $json->where('meta_form.id', (string)$metaForm->id)
                    ->whereNotNull('id')
                    ->etc()
            )
        );
    }

    public static function provideMissingRequiredPrompts()
    {
        return [
            'missing required prompts' => [
                'responses' => [
                    ['meta_prompt_id' => '2', 'response' => 'Input the second'],
                ],
                'errorFields' => ['input.responses'],
            ],
            'empty response when required' => [
                'responses' => [
                    ['meta_prompt_id' => '1', 'response' => ''],
                    ['meta_prompt_id' => '2', 'response' => 'Another test response'],
                ],
                'errorFields' => ['input.responses.0'],
            ],
        ];
    }

    #[Test]
    #[DataProvider('provideMissingRequiredPrompts')]
    public function cannotSaveWithoutRequiredPrompts($responses, $errorFields)
    {
        $metaForm = MetaForm::factory()
            ->for($this->publication)
            ->has(
                MetaPrompt::factory()
                    ->state(
                        new Sequence(
                            ['label' => 'Favorite color', 'type' => MetaPromptType::INPUT, 'required' => true],
                            ['label' => 'Favorite animal', 'type' => MetaPromptType::INPUT]
                        )
                    )
                    ->count(2)
            )
            ->create([
                'name' => 'Test Meta Form',
            ]);
        $this->actingAs($this->user);
        foreach ($responses as &$response) {
            $response['meta_prompt_id'] = $metaForm->metaPrompts[$response['meta_prompt_id'] - 1]->id;
        }

        $response = $this->graphQL(
            /** @lang GraphQL */
            '
            mutation SubmissionMetaFormUpdate($input: SubmissionMetaFormUpdate!) {
                submissionMetaFormUpdate(input: $input) {
                    id
                    meta_form {
                        id
                    }
                }
            }
            ',
            [
                'input' => [
                    'meta_form_id' => $metaForm->id,
                    'submission_id' => $this->userSubmission->id,
                    'responses' => $responses,
                ],
            ]
        );

        $errors = $response->json('errors');

        $this->assertNotEmpty($errors, 'Expected validation errors but none were returned.');
        $this->assertCount(1, $errors, 'Expected exactly one validation error but found: ' . count($errors));
        $json = $response->json('errors.0.extensions.validation');
        $this->assertNotEmpty($json, 'Expected validation errors but none were returned in the response.');
        foreach ($errorFields as $field) {
            $this->assertArrayHasKey($field, $json, "Expected validation error for field '$field' but it was not found in the response.");
        }
    }

    #[Test]
    public function cannotSaveInvalidSelectResponse()
    {
        $metaForm = MetaForm::factory()
            ->for($this->publication)
            ->has(
                MetaPrompt::factory()
                    ->state(
                        new Sequence(
                            ['label' => 'Favorite color', 'type' => MetaPromptType::SELECT, 'required' => true, 'options' => ['options' => ['red', 'blue', 'green']]],
                        )
                    )
                    ->count(1)
            )
            ->create([
                'name' => 'Test Meta Form',
            ]);
        $this->actingAs($this->user);

        $response = $this->graphQL(
            /** @lang GraphQL */
            '
            mutation SubmissionMetaFormUpdate($input: SubmissionMetaFormUpdate!) {
                submissionMetaFormUpdate(input: $input) {
                    id
                    meta_form {
                        id
                    }
                }
            }
            ',
            [
                'input' => [
                    'meta_form_id' => $metaForm->id,
                    'submission_id' => $this->userSubmission->id,
                    'responses' => [
                        ['meta_prompt_id' => $metaForm->metaPrompts[0]->id, 'response' => 'yellow'], // Invalid option
                    ],
                ],
            ]
        );

        $errors = $response->json('errors');
        $this->assertNotEmpty($errors, 'Expected validation errors but none were returned.');
        $this->assertCount(1, $errors, 'Expected exactly one validation error but found: ' . count($errors));
        $json = $response->json('errors.0.extensions.validation');
        $this->assertArrayHasKey('input.responses.0', $json, "Expected validation error for field 'input.responses.0' but it was not found in the response.");
    }

    #[Test]
    public function cannotSaveIfNotSubmitter()
    {
        $metaForm = MetaForm::factory()
            ->for($this->publication)
            ->has(
                MetaPrompt::factory()
                    ->state(
                        new Sequence(
                            ['label' => 'Favorite color', 'type' => MetaPromptType::INPUT, 'required' => true],
                            ['label' => 'Favorite animal', 'type' => MetaPromptType::INPUT]
                        )
                    )
                    ->count(2)
            )
            ->create([
                'name' => 'Test Meta Form',
            ]);

        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();
        $this->actingAs($anotherUser);

        $response = $this->graphQL(
            /** @lang GraphQL */
            '
            mutation SubmissionMetaFormUpdate($input: SubmissionMetaFormUpdate!) {
                submissionMetaFormUpdate(input: $input) {
                    id
                    meta_form {
                        id
                    }
                }
            }
            ',
            [
                'input' => [
                    'meta_form_id' => $metaForm->id,
                    'submission_id' => $this->userSubmission->id,
                    'responses' => [
                        ['meta_prompt_id' => $metaForm->metaPrompts[0]->id, 'response' => 'Input the first'],
                        ['meta_prompt_id' => $metaForm->metaPrompts[1]->id, 'response' => 'Another test response'],
                    ],
                ],
            ]
        );

        $errors = $response->json('errors');
        $this->assertNotEmpty($errors, 'Expected validation errors but none were returned.');
        $this->assertCount(1, $errors, 'Expected exactly one validation error but found: ' . count($errors));

        $this->assertStringContainsString('unauthorized', $errors[0]['message'], 'Expected authorization error but found: ' . $errors[0]['message']);
    }

    #[Test]
    public function cannotSaveIfNotInDraft()
    {
        $this->actingAs($this->user);
        $metaForm = MetaForm::factory()
            ->for($this->publication)
            ->has(
                MetaPrompt::factory()
                    ->state(
                        new Sequence(
                            ['label' => 'Favorite color', 'type' => MetaPromptType::INPUT, 'required' => true],
                            ['label' => 'Favorite animal', 'type' => MetaPromptType::INPUT]
                        )
                    )
                    ->count(2)
            )
            ->create([
                'name' => 'Test Meta Form',
            ]);

        $this->userSubmission->status = Submission::INITIALLY_SUBMITTED;
        $this->userSubmission->save();

        $response = $this->graphQL(
            /** @lang GraphQL */
            '
            mutation SubmissionMetaFormUpdate($input: SubmissionMetaFormUpdate!) {
                submissionMetaFormUpdate(input: $input) {
                    id
                    meta_form {
                        id
                    }
                }
            }
            ',
            [
                'input' => [
                    'meta_form_id' => $metaForm->id,
                    'submission_id' => $this->userSubmission->id,
                    'responses' => [
                        ['meta_prompt_id' => $metaForm->metaPrompts[0]->id, 'response' => 'Input the first'],
                        ['meta_prompt_id' => $metaForm->metaPrompts[1]->id, 'response' => 'Another test response'],
                    ],
                ],
            ]
        );

        $errors = $response->json('errors');
        $this->assertNotEmpty($errors, 'Expected errors but none were returned.');
        $this->assertCount(1, $errors, 'Expected exactly one error but found: ' . count($errors));

        $this->assertStringContainsString('unauthorized', $errors[0]['message'], 'Expected authorization error but found: ' . $errors[0]['message']);
    }
}
