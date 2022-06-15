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
        $name = $this->faker->streetName;
        //Occasionally streetName is longer than the validatable length for this field (20 chars).
        if (mb_strlen($name) > 20) {
            $name = substr($name, 0, 20);
        }

        return [
            'name' => $name,
            'description' => '<p>' . $this->faker->paragraph(3, true) . '</p>',
            'icon' => $this->faker->randomElement($this->iconOptions),
        ];
    }

    public $iconOptions = [
        'ac_unit', 'bungalow', 'corporate_fare', 'escalator_warning', 'grass', 'night_shelter', 'pool',
        'sports_bar', 'wash', 'airport_shuttle', 'business_center', 'cottage', 'family_restroom',
        'holiday_village', 'no_backpack', 'rice_bowl', 'stairs', 'water_damage', 'all_inclusive',
        'cabin', 'countertops', 'fence', 'hot_tub', 'no_cell', 'roofing', 'storefront', 'wheelchair_pickup',
        'apartment', 'carpenter', 'crib', 'fire_extinguisher', 'house', 'no_drinks', 'room_preferences', 'stroller',
        'baby_changing_station', 'casino', 'desk', 'fitness_center', 'house_siding', 'no_flash', 'room_service',
        'backpack', 'chalet', 'do_not_step', 'food_bank', 'houseboat', 'no_food', 'rv_hookup', 'tty',
        'balcony', 'charging_station', 'do_not_touch', 'foundation', 'iron', 'no_meeting_room', 'smoke_free',
        'bathtub', 'checkroom', 'dry', 'free_breakfast', 'kitchen', 'no_photography', 'smoking_rooms', 'vape_free',
        'beach_access', 'child_care', 'elevator', 'gite', 'meeting_room', 'no_stroller', 'soap', 'vaping_rooms',
        'bento', 'child_friendly', 'escalator', 'golf_course', 'microwave', 'other_houses', 'spa', 'villa',
    ];
}
