<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use App\Models\Submission;
use App\Models\SubmissionUser;
use App\Models\User;
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
            'user_id' => User::inRandomOrder()->first()->id,
            'submission_id' => Submission::inRandomOrder()->first()->id,
            'role_id' => Role::inRandomOrder()->first()->id,
        ];
    }
}
