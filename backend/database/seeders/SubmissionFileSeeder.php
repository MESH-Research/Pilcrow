<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SubmissionFile;
use Illuminate\Database\Seeder;

class SubmissionFileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SubmissionFile::factory()->count(2)->create([
            'submission_id' => 100,
        ]);
    }
}
