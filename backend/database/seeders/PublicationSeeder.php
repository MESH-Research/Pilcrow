<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Publication;
use App\Models\Role;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class PublicationSeeder extends Seeder
{
    /**
     * Run the database seed and create a publication with an administrator and editor.
     *
     * @param \App\Models\User $admin
     * @param \App\Models\User $editor
     * @return void
     */
    public function run(User $admin, User $editor)
    {
        $faker = Faker::create();
        Publication::factory()
        ->hasAttached(
            $admin,
            [
                'role_id' => Role::PUBLICATION_ADMINISTRATOR_ROLE_ID,
            ]
        )
        ->hasAttached(
            $editor,
            [
                'role_id' => Role::EDITOR_ROLE_ID,
            ]
        )
        ->create([
            'id' => 1,
            'name' => 'CCR Test Publication 1',
            'home_page_content' => $faker->paragraphs(2, true),
            'new_submission_content' => $faker->paragraphs(2, true),
        ]);
    }
}
