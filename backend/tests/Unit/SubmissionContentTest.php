<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Submission;
use App\Models\SubmissionContent;
use App\Models\SubmissionFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionContentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testPrimarySubmissionContentCanBeAccessedFromASubmission()
    {
        $submission = Submission::factory()->create();
        $submission_file = SubmissionFile::factory()->create([
            'submission_id' => $submission->id
        ]);
        $submission_content = SubmissionContent::factory()->create([
            'data' => 'Example content from PHPUnit',
            'submission_file_id' => $submission_file->id,
        ]);
        $submission->content_id = $submission_content->id;
        $data = $submission->content->data;
        $this->assertNotNull($data);
        $this->assertNotEmpty($data);
        $this->assertIsString($data);
    }

    /**
     * @return void
     */
    public function testAllSubmissionContentCanBeAccessedFromTheFilesOfASubmission()
    {
        $submission = Submission::factory()->create();
        for ($i = 0; $i < 3; $i++) {
            $submission_file = SubmissionFile::factory()->create([
                'submission_id' => $submission->id
            ]);
            $submission_content = SubmissionContent::factory()->create([
                'data' => 'Example content from PHPUnit ' . $i,
                'submission_file_id' => $submission_file->id,
            ]);
        }
        $submission->content_id = $submission_content->id;
        $this->assertEquals(3, $submission->files->count());
        $submission->files->map(function($file, $key){
            $this->assertEquals($file->content->data, 'Example content from PHPUnit ' . $key);
        });
    }
}
