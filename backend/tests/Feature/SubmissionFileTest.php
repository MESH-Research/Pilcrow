<?php
declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class SubmissionFileTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    /**
     * @return array
     */
    public function acceptedFileExtensionsProvider(): array
    {
        return [
            ['aiff'],
            ['avi'],
            ['csv'],
            ['doc'],
            ['docx'],
            ['gif'],
            ['html'],
            ['jpg'],
            ['m4a'],
            ['m4v'],
            ['mov'],
            ['mp3'],
            ['mp4'],
            ['odp'],
            ['ods'],
            ['pdf'],
            ['png'],
            ['pptx'],
            ['svg'],
            ['tiff'],
            ['tsv'],
            ['txt'],
            ['wmv'],
            ['xls'],
            ['xlsx'],
        ];
    }

    /**
     * @dataProvider acceptedFileExtensionsProvider
     * @param string $extension File extension value to test
     */
    public function testDocumentsCanBeUploaded(string $extension)
    {
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
            '0' => UploadedFile::fake()->create('test.' . $extension, 500),
        ];

        $this->multipartGraphQL($operations, $map, $file)
            ->assertJson([
                'data' => [
                    'upload' => true,
                ],
        ]);
    }
}
