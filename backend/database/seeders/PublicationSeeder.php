<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Publication;
use App\Models\StyleCriteria;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class PublicationSeeder extends Seeder
{
    /**
     * Run the database seed and create a publication with an administrator and editor.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $this->callOnce(UserSeeder::class);

        Publication::factory()
        ->hasAttached(User::firstWhere('username', 'publicationAdministrator'), [], 'publicationAdmins')
        ->hasAttached(User::firstWhere('username', 'publicationEditor'), [], 'editors')
        ->create([
            'id' => 1,
            'name' => 'CCR Test Publication 1',
            'home_page_content' => $faker->paragraphs(2, true),
            'new_submission_content' => $faker->paragraphs(2, true),
        ]);

        Publication::factory()
            ->count(5)
            ->has(
                StyleCriteria::factory()
                    ->count(4)
            )
            ->create();
    }
}
