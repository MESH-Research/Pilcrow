<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Publication;
use App\Models\Submission;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubmissionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Submission::class;

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
            'title' => $this->faker->sentence(10, true),
            'publication_id' => Publication::factory(),
            'file_upload' => UploadedFile::fake()->create('sample_file.'.$extension, 500),
        ];
    }
}
