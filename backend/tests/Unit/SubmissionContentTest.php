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
        $number_of_submissions = 3;
        $submissions = Submission::factory()->count($number_of_submissions)->create();
        $submissions->map(function ($submission, $index) {
            $submission_content = SubmissionContent::factory()->create([
                'data' => 'Example content from PHPUnit ' . $index,
                'submission_id' => $submission->first()->id,
            ]);
            $submission->content_id = $submission_content->id;
        });
        $first_data = $submissions->first()->content->data;
        $expected_first = 'Example content from PHPUnit 0';
        $this->assertEquals($expected_first, $first_data);

        $last_data = $submissions->last()->content->data;
        $expected_last = 'Example content from PHPUnit ' . ($number_of_submissions - 1);
        $this->assertEquals($expected_last, $last_data);
    }

    /**
     * @return void
     */
    public function testAllSubmissionContentCanBeAccessedFromASubmission()
    {
        $submission = Submission::factory()->create();
        for ($i = 0; $i < 3; $i++) {
            $submission_content = SubmissionContent::factory()->create([
                'data' => 'Example content from PHPUnit ' . $i,
                'submission_id' => $submission->id,
            ]);
        }
        $submission->content_id = $submission_content->id;
        $this->assertEquals(3, $submission->contentHistory->count());
        $submission->contentHistory->map(function ($content, $key) {
            $expected_data = 'Example content from PHPUnit ' . $key;
            $this->assertEquals($expected_data, $content->data);
        });
    }

    /**
     * @return void
     */
    public function testSubmissionContentCanBeAccessedFromASubmissionFile()
    {
        $number_of_submissions = 3;
        $submissions = Submission::factory()->count($number_of_submissions)->create();
        $submissions->map(function ($submission, $index) {
            $submission_content = SubmissionContent::factory()->create([
                'data' => 'Example content from PHPUnit ' . $index,
                'submission_id' => $submission->id,
            ]);
            SubmissionFile::factory()->create([
                'submission_id' => $submission->id,
                'content_id' => $submission_content->id,
            ]);
            $submission->content_id = $submission_content->id;
        });

        $first_data = $submissions->first()->files->first()->content->data;
        $expected_first = 'Example content from PHPUnit 0';
        $this->assertEquals($expected_first, $first_data);

        $last_data = $submissions->last()->files->first()->content->data;
        $expected_last = 'Example content from PHPUnit ' . ($number_of_submissions - 1);
        $this->assertEquals($expected_last, $last_data);
    }
}
