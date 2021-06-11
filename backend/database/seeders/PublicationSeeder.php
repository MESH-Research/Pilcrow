<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Publication;
use Illuminate\Database\Seeder;

class PublicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Publication::factory()->create([
            'id' => 1,
            'name' => 'Collaborative Review Organization',
        ]);
    }
}
