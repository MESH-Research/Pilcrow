<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SubmissionFileImportStatus;
use App\Jobs\ImportFileContent;
use App\Models\Publication;
use App\Models\Submission;
use App\Models\SubmissionContent;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
        $expected_first = '<p>Example content from PHPUnit 0</p>';
        $this->assertEquals($expected_first, $first_data);

        $last_data = $submissions->last()->content->data;
        $expected_last = '<p>Example content from PHPUnit ' . ($number_of_submissions - 1 . '</p>');
        $this->assertEquals($expected_last, $last_data);
    }

    public function testPurifierPassesEndnotes() {
        $content = <<<END
            <p>Small endnote test<a href="#fn1" class="footnote-ref" id="fnref1" role="doc-noteref"><sup>1</sup></a></p>
            <p>Another note<a href="#fn2" class="footnote-ref" id="fnref2" role="doc-noteref"><sup>2</sup></a></p>
            <section class="footnotes footnotes-end-of-document" role="doc-endnotes">
                <hr />
                <ol>
                    <li id="fn1" role="doc-endnote">
                        <p>Footnote 1<a href="#fnref1" class="footnote-back" role="doc-backlink">↩︎</a></p>
                    </li>
                    <li id="fn2" role="doc-endnote">
                        <p>Endnote 1<a href="#fnref2" class="footnote-back" role="doc-backlink">↩︎</a></p>
                    </li>
            </ol>
            </section>
            END;
        $submission = Submission::factory()->create();
        $submissionContent = SubmissionContent::factory()->create([
            'submission_id' => $submission->id,
            'data' => $content
        ]);
        $expected = <<<END
            <p>Small endnote test<a href="#fn1" id="fnref1" role="doc-noteref">1</a></p>
            <p>Another note<a href="#fn2" id="fnref2" role="doc-noteref">2</a></p>
            <section role="doc-endnotes">
                <hr>
                <ol>
                    <li id="fn1">
                        <p>Footnote 1<a href="#fnref1" role="doc-backlink">↩︎</a></p>
                    </li>
                    <li id="fn2">
                        <p>Endnote 1<a href="#fnref2" role="doc-backlink">↩︎</a></p>
                    </li>
            </ol>
            </section>
            END;
        $this->assertEquals($expected, $submissionContent->data);
    }

    /**
     * @return void
     */
    public function testAllSubmissionContentCanBeAccessedFromASubmission()
    {
        $submission = Submission::factory()->create();
        for ($i = 0; $i < 3; $i++) {
            $submission_content = SubmissionContent::factory()->create([
                'data' => '<p>Example content from PHPUnit ' . $i . '</p>',
                'submission_id' => $submission->id,
            ]);
        }
        $submission->content_id = $submission_content->id;
        $this->assertEquals(3, $submission->contentHistory->count());
        $submission->contentHistory->map(function ($content, $key) {
            $expected_data = '<p>Example content from PHPUnit ' . $key . '</p>';
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
                'data' => '<p>Example content from PHPUnit ' . $index . '</p>',
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
        $expected_first = '<p>Example content from PHPUnit 0</p>';
        $this->assertEquals($expected_first, $first_data);

        $last_data = $submissions->last()->files->first()->content->data;
        $expected_last = '<p>Example content from PHPUnit ' . ($number_of_submissions - 1 . '</p>');
        $this->assertEquals($expected_last, $last_data);
    }

    // TODO: Uncomment when jobs are are enabled
    // public function testNewSubmissionFileJobCreated()
    // {
    //     $this->beAppAdmin();
    //     $fileName = '/myfile.txt';
    //     Queue::fake();

    //     $file = SubmissionFile::factory()
    //         ->forSubmission()
    //         ->create([
    //             'file_upload' => $fileName,
    //         ]);

    //     Queue::assertPushed(function (ImportFileContent $job) use ($file) {
    //         return $job->file->id === $file->id;
    //     });
    // }

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
        $this->assertEquals('<p>New content for submission</p>', $file->submission->content->data);
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

    public function testImportFileJobIgnoresNonPendingFiles()
    {
        Queue::fake();
        $user = $this->beAppAdmin();
        $fileName = '/myfile.txt';

        $file = SubmissionFile::factory()
            ->forSubmission()
            ->create([
                'file_upload' => $fileName,
                'import_status' => SubmissionFileImportStatus::Processing(),
            ]);

        Pandoc::spy();

        $job = new ImportFileContent($file, $user);

        $job->handle();

        $file->refresh();
        Pandoc::shouldNotHaveReceived('run');
    }
}
