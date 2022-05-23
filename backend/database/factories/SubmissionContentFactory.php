<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Submission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubmissionContent>
 */
class SubmissionContentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'data' => $this->faker->paragraph(10, true),
            'submission_id' => Submission::factory(),
        ];
    }
}
