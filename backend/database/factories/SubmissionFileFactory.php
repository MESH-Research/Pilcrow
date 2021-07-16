<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Submission;
use App\Models\SubmissionFile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

class SubmissionFileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SubmissionFile::class;

    /**
     * @return array
     */
    public function acceptedFileExtensions(): array
    {
        return [
            'aiff',
            'avi',
            'csv',
            'doc',
            'docx',
            'gif',
            'html',
            'jpg',
            'm4a',
            'm4v',
            'mov',
            'mp3',
            'mp4',
            'odp',
            'ods',
            'pdf',
            'png',
            'pptx',
            'svg',
            'tiff',
            'tsv',
            'txt',
            'wmv',
            'xls',
            'xlsx',
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $extension = $this->faker->randomElement($this->acceptedFileExtensions());

        return [
            'submission_id' => Submission::factory(),
            'file_upload' => UploadedFile::fake()->create('sample_file.' . $extension, 500),
        ];
    }
}
