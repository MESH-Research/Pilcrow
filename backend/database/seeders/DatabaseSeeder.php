<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
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
        $admin = User::factory()->create([
            'username' => 'applicationAdminUser',
            'email' => 'applicationadministrator@ccrproject.dev',
            'name' => 'Application Administrator',
            'password' => Hash::make('adminPassword!@#'),
        ]);

        $publication_admin = User::factory()->create([
            'username' => 'publicationAdministrator',
            'email' => 'publicationAdministrator@ccrproject.dev',
            'name' => 'Publication Administrator',
            'password' => Hash::make('publicationadminPassword!@#'),
        ]);

        $editor = User::factory()->create([
            'username' => 'publicationEditor',
            'email' => 'publicationEditor@ccrproject.dev',
            'name' => 'Publication Editor',
            'password' => Hash::make('editorPassword!@#'),
        ]);

        $coordinator = User::factory()->create([
            'username' => 'reviewCoordinator',
            'email' => 'reviewCoordinator@ccrproject.dev',
            'name' => 'Review Coordinator for Submission',
            'password' => Hash::make('coordinatorPassword!@#'),
        ]);

        User::factory()->create([
            'username' => 'regularUser',
            'email' => 'regularuser@ccrproject.dev',
            'name' => 'Regular User',
            'password' => Hash::make('regularPassword!@#'),
        ]);

        $admin->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $publication_admin->assignRole(Role::PUBLICATION_ADMINISTRATOR);
        $editor->assignRole(Role::EDITOR);

        $publication_seeder = new PublicationSeeder();
        $publication_seeder->run();

        $submission_seeder = new SubmissionSeeder();
        $submission_seeder->run($coordinator);

        $submission_file_seeder = new SubmissionFileSeeder();
        $submission_file_seeder->run();
    }
}
