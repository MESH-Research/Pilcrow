<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\MetaPage;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubmissionMetaResponse>
 */
class SubmissionMetaResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'submission_id' => Submission::factory(),
            'meta_page_id' => MetaPage::factory(),
            'prompts' => json_encode([]),
            'responses' => json_encode([]),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
