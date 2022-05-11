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

        User::factory()->create([
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
        $publication_seeder->run($publication_admin, $editor);

        $submission_seeder = new SubmissionSeeder();
        $submission_seeder->run(100, 'CCR Test Submission 1');
        $submission_seeder->run(101, 'CCR Test Submission 2');

        $style_criterias_seeder = new StyleCriteriasSeeder();
        $style_criterias_seeder->run();

        $submission_file_seeder = new SubmissionFileSeeder();
        $submission_file_seeder->run();

        $submission_comment_seeder = new SubmissionCommentSeeder();
        $submission_comment_seeder->run();
        $submission_comment_seeder->run(1);
        $submission_comment_seeder->run(2, 2);
    }
}
