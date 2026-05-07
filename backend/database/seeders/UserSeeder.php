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
        $orcid = fn (string $id) => [
            'academic_profiles' => ['orcid_id' => $id],
        ];

        User::factory()->create([
            'username' => 'applicationAdminUser',
            'email' => 'applicationAdministrator@meshresearch.net',
            'name' => 'Application Administrator',
            'password' => Hash::make('adminPassword!@#'),
            'profile_metadata' => $orcid('0000-0002-1825-0097'),
        ])->assignRole(Role::APPLICATION_ADMINISTRATOR);

        User::factory()->create([
            'username' => 'publicationAdministrator',
            'email' => 'publicationAdministrator@meshresearch.net',
            'name' => 'Publication Administrator',
            'password' => Hash::make('publicationadminPassword!@#'),
            'profile_metadata' => $orcid('0000-0001-5109-3700'),
        ]);

        User::factory()->create([
            'username' => 'publicationEditor',
            'email' => 'publicationEditor@meshresearch.net',
            'name' => 'Publication Editor',
            'password' => Hash::make('editorPassword!@#'),
            'profile_metadata' => $orcid('0000-0002-1694-233X'),
        ]);

        User::factory()->create([
            'username' => 'reviewCoordinator',
            'email' => 'reviewCoordinator@meshresearch.net',
            'name' => 'Review Coordinator for Submission',
            'password' => Hash::make('coordinatorPassword!@#'),
            'profile_metadata' => $orcid('0000-0002-7099-2346'),
        ]);

        User::factory()->create([
            'username' => 'reviewer',
            'email' => 'reviewer@meshresearch.net',
            'name' => 'Reviewer for Submission',
            'password' => Hash::make('reviewerPassword!@#'),
            'profile_metadata' => $orcid('0000-0003-1234-5674'),
        ]);

        User::factory()->create([
            'username' => 'regularUser',
            'email' => 'regularuser@meshresearch.net',
            'name' => 'Regular User',
            'password' => Hash::make('regularPassword!@#'),
            'profile_metadata' => $orcid('0000-0002-8765-4327'),
        ]);
    }
}
