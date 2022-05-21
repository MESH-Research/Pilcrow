<?php
declare(strict_types=1);

namespace Tests\Feature;

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
    public function testSubmissionContentCanBeAccessedFromSubmission()
    {
        $submission_file = SubmissionFile::factory()->create();
        $submission_content = SubmissionContent::factory()->create([
            'content' => 'Example content',
            'submission_file_id' => $submission_file->id,
        ]);
        $submission_file->submission->content_id = $submission_content->id;
        $expected_content = $submission_file->submission->content->content;
        $this->assertNotNull($expected_content);
        $this->assertNotEmpty($expected_content);
        $this->assertIsString($expected_content);
    }
}
