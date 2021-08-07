<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\SubmissionUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubmissionUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SubmissionUser::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => null,
            'submission_id' => null,
            'role_id' => null,
        ];
    }
}
