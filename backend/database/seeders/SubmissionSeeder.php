<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\Submission;
use Illuminate\Database\Seeder;

class SubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Submission::factory()->hasAttached(
            User::where('username','regularUser')->firstOrFail(),
            [
                'role_id' => 6,
            ]
        )
        ->create([
            'title' => 'CCR Test Submission 1',
            'publication_id' => 1,
        ]);
    }
}
