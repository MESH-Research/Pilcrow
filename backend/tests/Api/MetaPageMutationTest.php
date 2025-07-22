<?php

namespace Tests\Api;

use App\Models\MetaPage;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\ApiTestCase;

class MetaPageMutationTest extends ApiTestCase
{
    // Your test methods go here
    public function testCreateMetaPage()
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

    public function testCreateMetaPrompt()
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
            $json->has('data.promptCreate')
                ->where('data.promptCreate.label', 'What is your favorite color?')
                ->where('data.promptCreate.required', true)
                ->where('data.promptCreate.type', 'INPUT')
                ->whereNotNull('data.promptCreate.id')
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

    public function testUpdateMetaPages()
    {
        $user = $this->bePubAdmin();

        $publication = $user->publications()->first();
        $metaPage = MetaPage::factory()->create([
            'publication_id' => $publication->id,
            'name' => 'Old Meta Page Name',
        ]);

        $response = $this->graphQL(
            /** @lang GraphQL */
            'mutation UpdateMetaPage($input: [UpdateMetaPageInput!]!) {
                metaPageUpdate(input: $input) {
                    id
                    name
                }
            }
                ',
            [
                'input' => [
                    [
                        'id' => $metaPage->id,
                        'name' => 'Updated Meta Page Name',
                    ]
                ]
            ]
        );
        var_dump($response->json());
        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data.metaPageUpdate')
                ->where('data.metaPageUpdate.name', 'Updated Meta Page Name')
                ->where('data.metaPageUpdate.id', (string)$metaPage->id)
                ->etc()
        );
    }
}
