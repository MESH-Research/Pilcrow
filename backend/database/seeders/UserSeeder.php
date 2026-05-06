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
            'email' => 'applicationAdministrator@meshresearch.net',
            'name' => 'Application Administrator',
            'password' => Hash::make('adminPassword!@#'),
            'profile_metadata' => self::profile(
                'Platform Administrator',
                'The Pilcrow Institute',
                'Platform stewardship, access control, and application-wide '
                    . 'configuration.',
                'Oversees account provisioning, role policy, and the global '
                    . 'settings that govern how all publications operate. '
                    . 'Coordinates with publication admins on compliance and '
                    . 'infrastructure concerns.'
            ),
        ])->assignRole(Role::APPLICATION_ADMINISTRATOR);

        User::factory()->create([
            'username' => 'publicationAdministrator',
            'email' => 'publicationAdministrator@meshresearch.net',
            'name' => 'Publication Administrator',
            'password' => Hash::make('publicationadminPassword!@#'),
            'profile_metadata' => self::profile(
                'Managing Editor',
                'Digital Humanities Review',
                'Editorial strategy, peer review workflow, and submission '
                    . 'triage.',
                'Responsible for the end-to-end submission pipeline at the '
                    . 'publication level — assigning coordinators, steering '
                    . 'contentious reviews, and keeping author communication '
                    . 'on track from initial submission through final '
                    . 'decision.'
            ),
        ]);

        User::factory()->create([
            'username' => 'publicationEditor',
            'email' => 'publicationEditor@meshresearch.net',
            'name' => 'Publication Editor',
            'password' => Hash::make('editorPassword!@#'),
            'profile_metadata' => self::profile(
                'Section Editor',
                'Digital Humanities Review',
                'Close reading, structural editing, and style consistency.',
                'Works alongside the managing editor to shepherd accepted '
                    . 'manuscripts through substantive edits, and occasionally '
                    . 'weighs in on borderline submissions where a second '
                    . 'editorial opinion helps.'
            ),
        ]);

        User::factory()->create([
            'username' => 'reviewCoordinator',
            'email' => 'reviewCoordinator@meshresearch.net',
            'name' => 'Review Coordinator for Submission',
            'password' => Hash::make('coordinatorPassword!@#'),
            'profile_metadata' => self::profile(
                'Review Coordinator',
                'Independent',
                'Assigning and pacing reviewer assignments.',
                'Matches submissions to reviewers with the right expertise, '
                    . 'keeps review deadlines visible, and nudges stalled '
                    . 'threads forward so authors aren\'t left waiting.'
            ),
        ]);

        User::factory()->create([
            'username' => 'reviewer',
            'email' => 'reviewer@meshresearch.net',
            'name' => 'Reviewer for Submission',
            'password' => Hash::make('reviewerPassword!@#'),
            'profile_metadata' => self::profile(
                'Peer Reviewer',
                'Independent',
                'Critical reading and constructive feedback.',
                'Reads assigned manuscripts carefully, flags methodological '
                    . 'or citation issues, and writes up inline comments plus '
                    . 'an overall recommendation for the coordinator.'
            ),
        ]);

        User::factory()->create([
            'username' => 'regularUser',
            'email' => 'regularuser@meshresearch.net',
            'name' => 'Regular User',
            'password' => Hash::make('regularPassword!@#'),
            'profile_metadata' => self::profile(
                'Graduate Researcher',
                'State University',
                'Emerging researcher; submits occasionally.',
                null
            ),
        ]);
    }

    /**
     * Build a minimal profile_metadata array matching the shape
     * `UserProfileCard.vue` expects. Only the narrative fields
     * (title, affiliation, specialization, biography) are set here —
     * the named seed accounts don't need fake social media links.
     *
     * @param string|null $positionTitle
     * @param string|null $affiliation
     * @param string|null $specialization
     * @param string|null $biography
     * @return array<string, mixed>
     */
    private static function profile(
        ?string $positionTitle,
        ?string $affiliation,
        ?string $specialization,
        ?string $biography,
    ): array {
        return [
            'position_title' => $positionTitle,
            'affiliation' => $affiliation,
            'specialization' => $specialization,
            'biography' => $biography,
            'websites' => [],
            'social_media' => [
                'twitter' => null,
                'linkedin' => null,
                'facebook' => null,
                'instagram' => null,
                'google' => null,
            ],
            'academic_profiles' => [
                'orcid_id' => null,
                'humanities_commons' => null,
            ],
        ];
    }
}
