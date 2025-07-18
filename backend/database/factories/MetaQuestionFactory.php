<?php

namespace Database\Factories;

use App\Enums\MetaQuestionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MetaQuestion>
 */
class MetaQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'caption' => $this->faker->sentence(),
            'meta_question_set_id' => \App\Models\MetaQuestionSet::factory(),
            'question' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(array_column(MetaQuestionType::cases(), 'value'))
        ];
    }
}
