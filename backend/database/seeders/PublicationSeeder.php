<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Publication;
use App\Models\Role;
use App\Models\StyleCriteria;
use App\Models\User;
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
        $this->callOnce(UserSeeder::class);

        Publication::factory()
        ->hasAttached(
            User::firstWhere('username', 'publicationAdministrator'),
            [
                'role_id' => Role::PUBLICATION_ADMINISTRATOR_ROLE_ID,
            ]
        )
        ->hasAttached(
            User::firstWhere('username', 'publicationEditor'),
            [
                'role_id' => Role::EDITOR_ROLE_ID,
            ]
        )
        ->create([
            'id' => 1,
            'name' => 'CCR Test Publication 1',
        ]);

        Publication::factory()->count(5)->has(StyleCriteria::factory()->count(4))->create();
    }
}
