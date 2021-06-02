<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Submission;

class SubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $submission = Submission::factory()->create([
            'title' => 'Collaborative Review Organization',
        ]);
    }
}
