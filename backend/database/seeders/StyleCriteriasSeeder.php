<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\StyleCriteria;
use Illuminate\Database\Seeder;

class StyleCriteriasSeeder extends Seeder
{
    /**
     * @param array $criteria
     * @return void
     */
    public function run($criteria)
    {
        StyleCriteria::factory()
            ->create([
                'name' => $criteria['name'],
                'publication_id' => 1,
                'description' => $criteria['description'],
                'icon' => $criteria['icon'],
            ]);
    }
}
