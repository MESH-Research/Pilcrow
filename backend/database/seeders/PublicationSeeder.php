<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Publication;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PublicationSeeder extends Seeder
{
    /**
     * Run the database seed and create a publication with an administrator and editor.
     *
     * @param $admin App\Models\User
     * @param $editor App\Models\User
     * @return void
     */
    public function run($admin, $editor)
    {
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
        ]);
    }
}
