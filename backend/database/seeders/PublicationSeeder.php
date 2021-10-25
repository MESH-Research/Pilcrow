<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Publication;
use Illuminate\Database\Seeder;

// TODO: Use constants for the ID usages
class PublicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($admin, $editor)
    {
        Publication::factory()
        ->hasAttached(
            $admin,
            [
                'role_id' => 2,
            ]
        )
        ->hasAttached(
            $editor,
            [
                'role_id' => 3,
            ]
        )
        ->create([
            'id' => 1,
            'name' => 'CCR Test Publication 1',
        ]);
    }
}
