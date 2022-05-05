<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\InlineComment;
use App\Models\StyleCriteria;
use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InlineComment>
 */
class InlineCommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InlineComment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $user = User::factory();
        $time = $this->faker->dateTimeBetween(Carbon::now()->subDays(5), Carbon::now());
        $style_criterias = StyleCriteria::inRandomOrder()
            ->limit(rand(1, 4))
            ->get()
            ->toArray();

        return [
            'submission_id' => Submission::factory(),
            'content' => $this->faker->paragraph(2, true),
            'created_at' => $time,
            'updated_at' => $time,
            'created_by' => $user,
            'updated_by' => $user,
            'style_criteria' => json_encode($style_criterias),
        ];
    }
}
