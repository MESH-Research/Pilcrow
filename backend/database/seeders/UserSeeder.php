<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'username' => 'applicationAdminUser',
            'email' => 'applicationadministrator@pilcrow.dev',
            'name' => 'Application Administrator',
            'password' => Hash::make('adminPassword!@#'),
        ])->assignRole(Role::APPLICATION_ADMINISTRATOR);

        User::factory()->create([
            'username' => 'publicationAdministrator',
            'email' => 'publicationAdministrator@pilcrow.dev',
            'name' => 'Publication Administrator',
            'password' => Hash::make('publicationadminPassword!@#'),
        ]);

        User::factory()->create([
            'username' => 'publicationEditor',
            'email' => 'publicationEditor@pilcrow.dev',
            'name' => 'Publication Editor',
            'password' => Hash::make('editorPassword!@#'),
        ]);

        User::factory()->create([
            'username' => 'reviewCoordinator',
            'email' => 'reviewCoordinator@pilcrow.dev',
            'name' => 'Review Coordinator for Submission',
            'password' => Hash::make('coordinatorPassword!@#'),
        ]);

        User::factory()->create([
            'username' => 'reviewer',
            'email' => 'reviewer@pilcrow.dev',
            'name' => 'Reviewer for Submission',
            'password' => Hash::make('reviewerPassword!@#'),
        ]);

        User::factory()->create([
            'username' => 'regularUser',
            'email' => 'regularuser@pilcrow.dev',
            'name' => 'Regular User',
            'password' => Hash::make('regularPassword!@#'),
        ]);
    }
}
