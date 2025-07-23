<?php

namespace Tests\Api;

use App\Enums\MetaPromptType;
use App\Models\MetaPage;
use App\Models\MetaPrompt;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Assert;
use Illuminate\Testing\Fluent\AssertableJson;
use Nuwave\Lighthouse\Exceptions\ValidationException;
use Tests\ApiTestCase;

class MetaPageMutationTest extends ApiTestCase
{
    use RefreshDatabase;

    // Your test methods go here
    public function testCanCreateMetaPage()
    {
        $user = $this->bePubAdmin();

        $publication = $user->publications()->first();

        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation CreateMetaPage($input: CreateMetaPageInput!) {
                metaPageCreate(input: $input) {
                    id
                    name
                }
            }
                ',
            [
                'input' => [
                    'publication_id' => $publication->id,
                    'name' => 'Default Meta Page',
                    'required' => false,
                ]
            ]
        );

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data.metaPageCreate')
                ->where('data.metaPageCreate.name', 'Default Meta Page')
                ->whereNotNull('data.metaPageCreate.id')
                ->etc()
        );
    }

    public function testOtherUserCannotCreateMetaPage()
    {
        $user = $this->bePubAdmin();
        $publication = $user->publications()->first();

        /** @var User $otherUser */
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser);
        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation CreateMetaPage($input: CreateMetaPageInput!) {
                    metaPageCreate(input: $input) {
                        id
                        name
                    }
                }
                ',
            [
                'input' => [
                    'publication_id' => $publication->id,
                    'name' => 'Unauthorized Meta Page',
                    'required' => false,
                ]
            ]
        );


        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('errors')
                ->missing('data.metaPageCreate.id')
                ->etc()
        );
    }

    public function testCanCreateMetaPrompt()
    {
        $user = $this->bePubAdmin();

        $publication = $user->publications()->first();
        $metaPage = MetaPage::factory()->create([
            'publication_id' => $publication->id,
            'name' => 'Sample Meta Page',
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
                    'meta_page_id' => $metaPage->id,
                    'label' => 'What is your favorite color?',
                    'required' => true,
                    'type' => 'INPUT'
                ]
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
        $metaPage = MetaPage::factory()->create([
            'publication_id' => $publication->id,
            'name' => 'Sample Meta Page',
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
                    'meta_page_id' => $metaPage->id,
                    'label' => 'What is your favorite color?',
                    'required' => true,
                    'type' => 'INPUT'
                ]
            ]
        );

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('errors')
                ->missing('data.promptCreate.id')
                ->etc()
        );
    }

    public function testCanUpdateMetaPages()
    {
        $user = $this->bePubAdmin();

        $publication = $user->publications()->first();
        $metaPage = MetaPage::factory()
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
                'name' => 'Old Meta Page Name',
            ])
            ->count(2)
            ->create();
        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation UpdateMetaPage($input: [UpdateMetaPageInput!]!) {
                metaPageUpdate(input: $input) {
                    id
                    name
                }
            }',
            [
                'input' => [
                    [
                        'id' => $metaPage[0]->id,
                        'name' => 'Updated Meta Page Name',
                    ],
                    [
                        'id' => $metaPage[1]->id,
                        'name' => 'Another Updated Meta Page Name',
                    ]
                ]
            ]
        );
        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data.metaPageUpdate')
                ->where('data.metaPageUpdate.0.name', 'Updated Meta Page Name')
                ->where('data.metaPageUpdate.0.id', (string)$metaPage[0]->id)
                ->etc()
        );
    }

    public function testCannotUpdateOtherMetaPages()
    {
        $unassignedPublication = Publication::factory()->create();

        $unassignedMetaPage = MetaPage::factory()
            ->state([
                'publication_id' => $unassignedPublication->id,
                'name' => 'Old Meta Page Name',
            ])
            ->count(2)
            ->create();
        $pubAdmin = $this->bePubAdmin();
        $metaPage = MetaPage::factory()
            ->state([
                'publication_id' => $pubAdmin->publications()->first()->id,
                'name' => 'Old Meta Page Name',
            ])
            ->create();

        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation UpdateMetaPage($input: [UpdateMetaPageInput!]!) {
                metaPageUpdate(input: $input) {
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
                        'id' => $unassignedMetaPage[0]->id,
                        'name' => 'Updated Meta Page Name',
                    ],
                    [
                        'id' => $metaPage->id,
                        'name' => 'Updated Meta Page Name',
                    ]
                ]
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
        $metaPage = MetaPage::factory()
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
                'name' => 'Old Meta Page Name',
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
                        'id' => $metaPage->metaPrompts[0]->id,
                        'label' => 'Updated Prompt 1',
                    ],
                    [
                        'id' => $metaPage->metaPrompts[1]->id,
                        'label' => 'Another Updated Prompt 2',
                    ]
                ]
            ]
        );

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data.metaPromptUpdate')
                ->where('data.metaPromptUpdate.0.label', 'Updated Prompt 1')
                ->where('data.metaPromptUpdate.0.id', (string)$metaPage->metaPrompts[0]->id)
                ->etc()
        );
    }

    public function testOtherUsersCannotUpdateMetaPrompts()
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $publication = Publication::factory()
            ->has(
                MetaPage::factory()
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
                        'id' => $publication->metaPages[0]->metaPrompts[0]->id,
                        'label' => 'Unauthorized Update',
                    ]
                ]
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
        $metaPage = MetaPage::factory()
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
                'name' => 'Old Meta Page Name',
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
                'id' => $metaPage->metaPrompts[0]->id,
            ]
        );

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data.metaPromptDelete')
                ->where('data.metaPromptDelete.id', (string)$metaPage->metaPrompts[0]->id)
                ->etc()
        );

        $this->assertNull(MetaPrompt::find($metaPage->metaPrompts[0]->id));
    }

    public function testOtherCannotDeleteMetaPrompts()
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $publication = Publication::factory()
            ->has(
                MetaPage::factory()
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
                'id' => $publication->metaPages[0]->metaPrompts[0]->id,
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
        $metaPage = MetaPage::factory()
            ->state([
                'publication_id' => $publication->id,
                'name' => 'Meta Page to Delete',
            ])
            ->create();

        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation DeleteMetaPage($id: ID!) {
                metaPageDelete(id: $id) {
                    id
                }
            }',
            [
                'id' => $metaPage->id,
            ]
        );

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data.metaPageDelete')
                ->where('data.metaPageDelete.id', (string)$metaPage->id)
                ->etc()
        );

        $this->assertNull(MetaPage::find($metaPage->id));
    }

    public function testOthersCannotDeleteMetaPromptPage()
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actingAs($user);

        $publication = Publication::factory()
            ->has(
                MetaPage::factory()
                    ->has(MetaPrompt::factory()->count(3))
            )
            ->create();

        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation DeleteMetaPage($id: ID!) {
                metaPageDelete(id: $id) {
                    id
                }
            }',
            [
                'id' => $publication->metaPages[0]->id,
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
