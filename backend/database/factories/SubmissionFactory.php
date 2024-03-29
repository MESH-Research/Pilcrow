<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
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
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = auth()->user();
        if (!$user) {
            $user = User::factory()->create();
        }

        return [
            'title' => $this->faker->sentence(10, true),
            'publication_id' => Publication::factory(),
            'status' => Submission::DRAFT,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ];
    }
}
