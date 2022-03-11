<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\StyleCriteria;
use Illuminate\Database\Eloquent\Factories\Factory;

class StyleCriteriaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StyleCriteria::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->paragraph(3, true),
        ];
    }
}
