<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\SubmissionFileSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'username' => 'regularUser',
            'email' => 'regularuser@ccrproject.dev',
            'name' => 'Regular User',
            'password' => Hash::make('regularPassword!@#'),
        ]);

        $user = User::factory()->create([
            'username' => 'applicationAdminUser',
            'email' => 'applicationadministrator@ccrproject.dev',
            'name' => 'Application Admin User',
            'password' => Hash::make('adminPassword!@#'),
        ]);
        $user->assignRole(Role::APPLICATION_ADMINISTRATOR);

        $publication_seeder = new PublicationSeeder();
        $publication_seeder->run();

        $submission_seeder = new SubmissionSeeder();
        $submission_seeder->run();

        $submission_file_seeder = new SubmissionFileSeeder();
        $submission_file_seeder->run();
    }
}
