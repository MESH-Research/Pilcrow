<?php
declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use PHPUnit\Framework\TestCase;

class FileUploadTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_example()
    {
        $this->assertTrue(true);
    }

    public function testPdfDocumentUpload()
    {
        // Storage::fake('submissions');

        $operations = [
            'operationName' => 'upload',
            'query' => 'mutation upload ($file: Upload!) {
                upload (file: $file)
            }',
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

        $this->multipartGraphQL($operations, $map, $file);

        // Storage::disk('submissions')->assertExists($file[0]->hashName());
    }
}
