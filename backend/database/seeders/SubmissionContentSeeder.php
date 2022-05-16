<?php

namespace Database\Seeders;

use App\Models\SubmissionContent;
use Illuminate\Database\Seeder;

class SubmissionContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $submission_content = SubmissionContent::factory()->create([
          'content' => 'Example content',
          'submission_file_id' => 1,
      ]);

    }
}
