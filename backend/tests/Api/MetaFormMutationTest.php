<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Enums\MetaPromptType;
use App\Models\MetaForm;
use App\Models\MetaPrompt;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\ApiTestCase;

class MetaFormMutationTest extends ApiTestCase
{
    use RefreshDatabase;

    // Your test methods go here

    public function testCanCreateMetaForm()
    {
        $user = $this->bePubAdmin();

        $publication = $user->publications()->first();

        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation CreateMetaForm($input: CreateMetaFormInput!) {
                metaFormCreate(input: $input) {
                    id
                    name
                }
            }
                ',
            [
                'input' => [
                    'publication_id' => $publication->id,
                    'name' => 'Default Meta Form',
                    'required' => false,
                ],
            ]
        );

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data.metaFormCreate')
                ->where('data.metaFormCreate.name', 'Default Meta Form')
                ->whereNotNull('data.metaFormCreate.id')
                ->etc()
        );
    }

    public function testOtherUserCannotCreateMetaForm()
    {
        $user = $this->bePubAdmin();
        $publication = $user->publications()->first();

        /** @var User $otherUser */
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser);
        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation CreateMetaForm($input: CreateMetaFormInput!) {
                    metaFormCreate(input: $input) {
                        id
                        name
                    }
                }
                ',
            [
                'input' => [
                    'publication_id' => $publication->id,
                    'name' => 'Unauthorized Meta Form',
                    'required' => false,
                ],
            ]
        );

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('errors')
                ->missing('data.metaFormCreate.id')
                ->etc()
        );
    }

    public function testCanCreateMetaPrompt()
    {
        $user = $this->bePubAdmin();

        $publication = $user->publications()->first();
        $metaForm = MetaForm::factory()->create([
            'publication_id' => $publication->id,
            'name' => 'Sample Meta Form',
        ]);

        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation CreateMetaPrompt($input: CreateMetaPromptInput!) {
                metaPromptCreate(input: $input) {
                    id
                    label
                    required
                    type
                }
            }
                ',
            [
                'input' => [
                    'meta_form_id' => $metaForm->id,
                    'label' => 'What is your favorite color?',
                    'required' => true,
                    'type' => 'INPUT',
                ],
            ]
        );

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data.metaPromptCreate')
                ->where('data.metaPromptCreate.label', 'What is your favorite color?')
                ->where('data.metaPromptCreate.required', true)
                ->where('data.metaPromptCreate.type', 'INPUT')
                ->whereNotNull('data.metaPromptCreate.id')
                ->etc()
        );
    }

    public function testOtherUserCannotCreateMetaPrompt()
    {
        $user = $this->bePubAdmin();

        $publication = $user->publications()->first();
        $metaForm = MetaForm::factory()->create([
            'publication_id' => $publication->id,
            'name' => 'Sample Meta Form',
        ]);

        /** @var User $otherUser */
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser);

        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation CreateMetaPrompt($input: CreateMetaPromptInput!) {
                promptCreate(input: $input) {
                    id
                    label
                    required
                    type
                }
            }
                ',
            [
                'input' => [
                    'meta_form_id' => $metaForm->id,
                    'label' => 'What is your favorite color?',
                    'required' => true,
                    'type' => 'INPUT',
                ],
            ]
        );

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('errors')
                ->missing('data.promptCreate.id')
                ->etc()
        );
    }

    public function testCanUpdateMetaForms()
    {
        $user = $this->bePubAdmin();

        $publication = $user->publications()->first();
        $metaForm = MetaForm::factory()
            ->has(
                MetaPrompt::factory()
                    ->state(
                        new Sequence(
                            ['label' => 'Prompt 1', 'required' => true, 'type' => MetaPromptType::INPUT],
                            ['label' => 'Prompt 2', 'required' => false, 'type' => MetaPromptType::INPUT],
                        )
                    )
                    ->count(2)
            )
            ->state([
                'publication_id' => $publication->id,
                'name' => 'Old Meta Form Name',
            ])
            ->count(2)
            ->create();
        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation UpdateMetaForm($input: [UpdateMetaFormInput!]!) {
                metaFormUpdate(input: $input) {
                    id
                    name
                }
            }',
            [
                'input' => [
                    [
                        'id' => $metaForm[0]->id,
                        'name' => 'Updated Meta Form Name',
                    ],
                    [
                        'id' => $metaForm[1]->id,
                        'name' => 'Another Updated Meta Form Name',
                    ],
                ],
            ]
        );
        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data.metaFormUpdate')
                ->where('data.metaFormUpdate.0.name', 'Updated Meta Form Name')
                ->where('data.metaFormUpdate.0.id', (string)$metaForm[0]->id)
                ->etc()
        );
    }

    public function testCannotUpdateOtherMetaForms()
    {
        $unassignedPublication = Publication::factory()->create();

        $unassignedMetaForm = MetaForm::factory()
            ->state([
                'publication_id' => $unassignedPublication->id,
                'name' => 'Old Meta Form Name',
            ])
            ->count(2)
            ->create();
        $pubAdmin = $this->bePubAdmin();
        $metaForm = MetaForm::factory()
            ->state([
                'publication_id' => $pubAdmin->publications()->first()->id,
                'name' => 'Old Meta Form Name',
            ])
            ->create();

        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation UpdateMetaForm($input: [UpdateMetaFormInput!]!) {
                metaFormUpdate(input: $input) {
                    id
                    name
                    publication {
                        id
                    }

                }
            }',
            [
                'input' => [
                    [
                        'id' => $unassignedMetaForm[0]->id,
                        'name' => 'Updated Meta Form Name',
                    ],
                    [
                        'id' => $metaForm->id,
                        'name' => 'Updated Meta Form Name',
                    ],
                ],
            ]
        );
        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('errors')
                ->where('errors.0.message', 'This action is unauthorized.')
                ->etc()
        );
    }

    public function testCanUpdateMetaPrompts()
    {
        $user = $this->bePubAdmin();

        $publication = $user->publications()->first();
        $metaForm = MetaForm::factory()
            ->has(
                MetaPrompt::factory()
                    ->state(
                        new Sequence(
                            ['label' => 'Prompt 1', 'required' => true, 'type' => MetaPromptType::INPUT],
                            ['label' => 'Prompt 2', 'required' => false, 'type' => MetaPromptType::INPUT],
                        )
                    )
                    ->count(2)
            )
            ->state([
                'publication_id' => $publication->id,
                'name' => 'Old Meta Form Name',
            ])
            ->create();

        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation UpdateMetaPrompt($input: [UpdateMetaPromptInput!]!) {
                metaPromptUpdate(input: $input) {
                    id
                    label
                }
            }',
            [
                'input' => [
                    [
                        'id' => $metaForm->metaPrompts[0]->id,
                        'label' => 'Updated Prompt 1',
                    ],
                    [
                        'id' => $metaForm->metaPrompts[1]->id,
                        'label' => 'Another Updated Prompt 2',
                    ],
                ],
            ]
        );

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data.metaPromptUpdate')
                ->where('data.metaPromptUpdate.0.label', 'Updated Prompt 1')
                ->where('data.metaPromptUpdate.0.id', (string)$metaForm->metaPrompts[0]->id)
                ->etc()
        );
    }

    public function testOtherUsersCannotUpdateMetaPrompts()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $publication = Publication::factory()
            ->has(
                MetaForm::factory()
                    ->has(MetaPrompt::factory()->count(3))
            )
            ->create();

        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation UpdateMetaPrompt($input: [UpdateMetaPromptInput!]!) {
                metaPromptUpdate(input: $input) {
                    id
                    label
                }
            }',
            [
                'input' => [
                    [
                        'id' => $publication->metaForms[0]->metaPrompts[0]->id,
                        'label' => 'Unauthorized Update',
                    ],
                ],
            ]
        );

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('errors')
                ->where('errors.0.message', 'This action is unauthorized.')
                ->etc()
        );
    }

    public function testCanDeletePrompts()
    {
        $user = $this->bePubAdmin();

        $publication = $user->publications()->first();
        $metaForm = MetaForm::factory()
            ->has(
                MetaPrompt::factory()
                    ->state(
                        new Sequence(
                            ['label' => 'Prompt 1', 'required' => true, 'type' => MetaPromptType::INPUT],
                            ['label' => 'Prompt 2', 'required' => false, 'type' => MetaPromptType::INPUT],
                        )
                    )
                    ->count(2)
            )
            ->state([
                'publication_id' => $publication->id,
                'name' => 'Old Meta Form Name',
            ])
            ->create();

        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation DeleteMetaPrompt($id: ID!) {
                metaPromptDelete(id: $id) {
                    id
                }
            }',
            [
                'id' => $metaForm->metaPrompts[0]->id,
            ]
        );

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data.metaPromptDelete')
                ->where('data.metaPromptDelete.id', (string)$metaForm->metaPrompts[0]->id)
                ->etc()
        );

        $this->assertNull(MetaPrompt::find($metaForm->metaPrompts[0]->id));
    }

    public function testOtherCannotDeleteMetaPrompts()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $publication = Publication::factory()
            ->has(
                MetaForm::factory()
                    ->has(MetaPrompt::factory()->count(3))
            )
            ->create();

        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation DeleteMetaPrompt($id: ID!) {
                metaPromptDelete(id: $id) {
                    id
                }
            }',
            [
                'id' => $publication->metaForms[0]->metaPrompts[0]->id,
            ]
        );

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('errors')
                ->where('errors.0.message', 'This action is unauthorized.')
                ->etc()
        );
    }

    public function testCanDeleteMetaPromptPage()
    {
        $user = $this->bePubAdmin();

        $publication = $user->publications()->first();
        $metaForm = MetaForm::factory()
            ->state([
                'publication_id' => $publication->id,
                'name' => 'Meta Form to Delete',
            ])
            ->create();

        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation DeleteMetaForm($id: ID!) {
                metaFormDelete(id: $id) {
                    id
                }
            }',
            [
                'id' => $metaForm->id,
            ]
        );

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data.metaFormDelete')
                ->where('data.metaFormDelete.id', (string)$metaForm->id)
                ->etc()
        );

        $this->assertNull(MetaForm::find($metaForm->id));
    }

    public function testOthersCannotDeleteMetaPromptPage()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $publication = Publication::factory()
            ->has(
                MetaForm::factory()
                    ->has(MetaPrompt::factory()->count(3))
            )
            ->create();

        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation DeleteMetaForm($id: ID!) {
                metaFormDelete(id: $id) {
                    id
                }
            }',
            [
                'id' => $publication->metaForms[0]->id,
            ]
        );

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('errors')
                ->where('errors.0.message', 'This action is unauthorized.')
                ->etc()
        );
    }
}
