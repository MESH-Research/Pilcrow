<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SubmissionFileImportStatus;
use App\Jobs\ImportFileContent;
use App\Models\Submission;
use App\Models\SubmissionContent;
use App\Models\SubmissionFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Pandoc\Facades\Pandoc;
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
                'file_upload' => '/tmp/testfile.txt',
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

    public function testNewSubmissionFileJobCreated()
    {
        $this->beAppAdmin();
        $fileName = '/myfile.txt';
        Queue::fake();

        $file = SubmissionFile::factory()
            ->forSubmission()
            ->create([
                'file_upload' => $fileName,
            ]);

        Queue::assertPushed(function (ImportFileContent $job) use ($file) {
            return $job->file->id === $file->id;
        });
    }

    public function testImportFileContentCallsPandoc()
    {
        Queue::fake();
        $user = $this->beAppAdmin();
        $fileName = '/myfile.txt';

        $file = SubmissionFile::factory()
            ->forSubmission()
            ->create([
                'file_upload' => $fileName,
            ]);

        $pandoc = $this->getMockBuilder(\Pandoc\Pandoc::class)
            ->onlyMethods(['run'])
            ->getMock();

        $pandoc->expects($this->once())
            ->method('run')
            ->willReturn('New content for submission');

        Pandoc::shouldReceive('inputFile')
            ->once()
            ->with(Mockery::pattern('%' . $fileName . '$%'))
            ->andReturn($pandoc);

        $job = new ImportFileContent($file, $user);

        $job->handle();

        $file->refresh();
        $this->assertNotEmpty($file->content_id);
        $this->assertEquals(SubmissionFileImportStatus::Success(), $file->import_status);
        $this->assertEquals('New content for submission', $file->submission->content->data);
    }

    public function testImportFileJobErrorReporting()
    {
        Queue::fake();
        $user = $this->beAppAdmin();
        $fileName = '/myfile.txt';

        $file = SubmissionFile::factory()
            ->forSubmission()
            ->create([
                'file_upload' => $fileName,
            ]);

        $pandoc = $this->getMockBuilder(\Pandoc\Pandoc::class)
            ->onlyMethods(['run'])
            ->getMock();

        $pandoc->expects($this->once())
            ->method('run')
            ->willThrowException(new \App\Exceptions\EmptyContentOnImport());

        Pandoc::shouldReceive('inputFile')
            ->once()
            ->with(Mockery::pattern('%' . $fileName . '$%'))
            ->andReturn($pandoc);

        $job = new ImportFileContent($file, $user);

        $job->handle();

        $file->refresh();
        $this->assertEmpty($file->content_id);
        $this->assertEquals(SubmissionFileImportStatus::Failure(), $file->import_status);
        $this->assertStringContainsString('EmptyContentOnImport', $file->error_message);
        $this->assertNotEquals($file->id, $file->submission->content_id);
    }
}
