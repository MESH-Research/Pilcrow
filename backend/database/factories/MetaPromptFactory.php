<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MetaPromptType;
use App\Models\MetaForm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MetaPrompt>
 */
class MetaPromptFactory extends Factory
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
            'meta_form_id' => MetaForm::factory(),
            'label' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(array_column(MetaPromptType::cases(), 'value')),
        ];
    }
}
