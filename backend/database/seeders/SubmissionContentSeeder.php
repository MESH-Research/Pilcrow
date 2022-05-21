<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Submission;
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
          'data' => 'Example content',
          'submission_file_id' => 1,
        ]);
        Submission::find('100')->content_id = $submission_content->id;
    }
}
