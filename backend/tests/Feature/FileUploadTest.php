<?php
declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class FileUploadTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    public function testPdfDocumentsCanBeUploaded()
    {
        // Storage::fake('submissions');

        $operations = [
            'query' => '
                mutation ($file: Upload!) {
                    upload(file: $file)
                }
            ',
            'variables' => [
                'file' => null,
            ],
        ];

        $map = [
            '0' => ['variables.file'],
        ];

        $file = [
            '0' => UploadedFile::fake()->create('test.pdf', 500),
        ];

        $response = $this->multipartGraphQL($operations, $map, $file)
            ->assertJson([
                'data' => [
                    'upload' => true,
                ],
            ]);
        print_r($response->decodeResponseJson());

        // Storage::disk('submissions')->assertExists($file[0]->hashName());
    }
}
