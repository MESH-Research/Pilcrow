<?php

namespace Tests\Feature;

use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\SubmissionContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SubmissionContentTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testSubmissionContentCanBeAccessedFromSubmission()
    {
        $submission_file = SubmissionFile::factory()->create();
        $submission_content = SubmissionContent::factory()->create([
            'content' => 'Example content',
            'submission_file_id' => $submission_file->id,
        ]);
        print_r($submission_file->toArray());
        print("******");
        print("******");
        print_r($submission_content->id);
        $submission_file->submission->content_id = $submission_content->id;
        print("****after");
        print_r($submission_file->submission->content->toArray());
        /*
        $expected_content = $submission_file->submission->content;
        $this->assertNotNull($expected_content); 
        $this->asserNotEmpty($expected_content); 
        $this->asserIsString($expected_content); 
        */
    }
}
