<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Submission;
use App\Models\SubmissionFile;
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
    public function testCreateSubmissionFileRecordsViaGraphqlEndpoint(string $extension)
    {
        $submission = Submission::factory()->create();
        $operations = [
            'operationName' => 'CreateSubmissionFile',
            'query' => '
                mutation CreateSubmissionFile($submission_id: ID!, $file_upload: Upload!) {
                    createSubmissionFile(
                        input: {
                            submission_id: $submission_id,
                            file_upload: $file_upload
                        }
                    ) {
                        submission_id
                        file_upload
                    }
                }
            ',
            'variables' => [
                'submission_id' => $submission->id,
                'file_upload' => null,
            ],
        ];
        $map = [
            '0' => ['variables.file_upload'],
        ];
        $file = [
            '0' => UploadedFile::fake()->create('test.' . $extension, 500),
        ];
        $this->multipartGraphQL($operations, $map, $file)
            ->assertJson([
                'data' => [
                    'createSubmissionFile' => [
                        'submission_id' => (string)$submission->id,
                        'file_upload' => true,
                    ],
                ],
            ]);
        $record = SubmissionFile::where('submission_id', $submission->id)->get();
        $this->assertGreaterThan(0, count($record));
    }
}
