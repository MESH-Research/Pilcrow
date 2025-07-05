<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubmissionAssignment>
 */
class SubmissionAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_id' => Role::SUBMITTER,
            'user_id' => User::factory(),
            'submission_id' => Submission::factory(),
        ];
    }

    public function reviewer(): static
    {
        return $this->state([
            'role_id' => Role::REVIEWER,
        ]);
    }

    public function submitter(): static
    {
        return $this->state([
            'role_id' => Role::SUBMITTER,
        ]);
    }

    public function reviewCoordinator(): static
    {
        return $this->state([
            'role_id' => Role::REVIEW_COORDINATOR,
        ]);
    }
}
