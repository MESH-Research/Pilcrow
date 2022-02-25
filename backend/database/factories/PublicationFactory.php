<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Publication;
use Illuminate\Database\Eloquent\Factories\Factory;

class PublicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Publication::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->company(),
            'is_publically_visible' => true,
        ];
    }

    /**
     * Factory State for public visibility
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function hidden()
    {
        return $this->state(function (array $_) {
            return [
                'is_publically_visible' => false,
            ];
        });
    }
}
