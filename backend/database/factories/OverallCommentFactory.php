<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\OverallComment;
use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OverallComment>
 */
class OverallCommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OverallComment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $user = User::factory();
        $time = $this->faker->dateTimeBetween(Carbon::now()->subDays(5), Carbon::now());
        return [
            'submission_id' => Submission::factory(),
            'content' => $this->faker->paragraph(2, true),
            'created_at' => $time,
            'updated_at' => $time,
            'created_by' => $user,
            'updated_by' => $user,
        ];
    }
}
