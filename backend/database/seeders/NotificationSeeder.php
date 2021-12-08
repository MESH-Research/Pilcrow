<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param \App\Models\User $review_coordinator
     * @return void
     */
    public function run($review_coordinator)
    {
        Submission::factory()
            ->hasAttached(
                User::where('username', 'regularUser')->firstOrFail(),
                [
                    'role_id' => 6,
                ]
            )
            ->hasAttached(
                $review_coordinator,
                [
                    'role_id' => 4,
                ]
            )
            ->create([
                'body' => "A submission has been created.",
                'action' => "Visit CCR",
                'url' => "/",
                'submission_id' => 100
            ]);
    }
}

