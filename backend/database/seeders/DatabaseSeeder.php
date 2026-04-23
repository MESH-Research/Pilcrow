<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            PublicationSeeder::class,
            StyleCriteriasSeeder::class,
            SubmissionSeeder::class,
            InlineCommentSeeder::class,
            OverallCommentSeeder::class,
            // ExportPreviewSeeder hard-codes submission id 113, so it
            // must claim that id before DashboardDemoSeeder extends the
            // auto-increment past it.
            ExportPreviewSeeder::class,
            DashboardDemoSeeder::class,
        ]);
    }
}
