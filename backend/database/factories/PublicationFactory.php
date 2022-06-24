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
            'is_publicly_visible' => true,
            'home_page_content' => $this->faker->content(1),
            'new_submission_content' => $this->faker->paragraphs(2, true),
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
                'is_publicly_visible' => false,
            ];
        });
    }
}
