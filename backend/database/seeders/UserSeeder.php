<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Auth\GlobalRole;
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
        $orcid = fn(string $id) => [
            'academic_profiles' => ['orcid_id' => $id],
        ];

        User::factory()->create([
            'username' => 'applicationAdminUser',
            'email' => 'applicationAdministrator@meshresearch.net',
            'name' => 'Application Administrator',
            'password' => Hash::make('adminPassword!@#'),
            'profile_metadata' => $orcid('0000-0002-1825-0097'),
        ])->assignRole(GlobalRole::ApplicationAdministrator);

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

        User::factory()->create([
            'username' => 'naomiOkafor',
            'email' => 'naomi.okafor@meshresearch.net',
            'name' => 'Naomi Okafor',
            'password' => Hash::make('regularPassword!@#'),
            'profile_metadata' => $orcid('0000-0001-7421-9038'),
        ]);

        User::factory()->create([
            'username' => 'leaMarchetti',
            'email' => 'lea.marchetti@meshresearch.net',
            'name' => 'Léa Marchetti',
            'password' => Hash::make('regularPassword!@#'),
            'profile_metadata' => $orcid('0000-0003-2154-6781'),
        ]);

        User::factory()->create([
            'username' => 'hiroshiTanaka',
            'email' => 'hiroshi.tanaka@meshresearch.net',
            'name' => 'Hiroshi Tanaka',
            'password' => Hash::make('regularPassword!@#'),
            'profile_metadata' => $orcid('0000-0002-4498-1126'),
        ]);

        User::factory()->create([
            'username' => 'priyaRamanathan',
            'email' => 'priya.ramanathan@meshresearch.net',
            'name' => 'Priya Ramanathan',
            'password' => Hash::make('regularPassword!@#'),
            'profile_metadata' => $orcid('0000-0001-9032-7714'),
        ]);

        User::factory()->beta()->create([
            'username' => 'betaUser',
            'email' => 'betaUser@meshresearch.net',
            'name' => 'Beta User',
            'password' => Hash::make('betaPassword!@#'),
        ]);
    }
}
