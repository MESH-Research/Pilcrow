<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Publication;
use App\Models\StyleCriteria;
use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DashboardDemoSeeder extends Seeder
{
    /**
     * Seed a realistic publication with diverse submissions for
     * exploring the publication dashboard UI.
     *
     * @return void
     */
    public function run()
    {
        $this->callOnce(UserSeeder::class);

        $admin = User::firstWhere('username', 'publicationAdministrator');
        $editor = User::firstWhere('username', 'publicationEditor');

        $publication = Publication::factory()
            ->hasAttached($admin, [], 'publicationAdmins')
            ->hasAttached($editor, [], 'editors')
            ->has(StyleCriteria::factory()->count(3))
            ->create([
                'name' => 'A - Digital Humanities Review',
                'is_accepting_submissions' => true,
            ]);

        $submissions = $this->submissionData();

        // Build a shared pool of users so the same people appear across
        // multiple submissions — closer to what a real publication's
        // reviewer/coordinator rosters look like.
        $submitterPool = User::factory()->count(12)->create();
        $coordinatorPool = User::factory()->count(3)->create();
        $reviewerPool = User::factory()->count(6)->create();

        foreach ($submissions as $data) {
            $submitter = $submitterPool->random();
            $coordinator = $coordinatorPool->random();
            $reviewers = $reviewerPool->random(random_int(1, 4));

            $factory = Submission::factory()
                ->for($publication)
                ->hasAttached($submitter, [], 'submitters')
                ->hasAttached($coordinator, [], 'reviewCoordinators');

            foreach ($reviewers as $reviewer) {
                $factory = $factory->hasAttached(
                    $reviewer,
                    [],
                    'reviewers'
                );
            }

            $created = Carbon::now()
                ->subDays($data['daysAgo'])
                ->subHours(random_int(0, 12));
            $diff = max(1, (int)$created->diffInMinutes(now()));
            $updated = $created->copy()
                ->addMinutes(random_int(0, $diff));

            $factory->create([
                'title' => $data['title'],
                'status' => $data['status'],
                'created_by' => $submitter->id,
                'updated_by' => $submitter->id,
                'created_at' => $created,
                'updated_at' => $updated,
            ]);
        }
    }

    /**
     * Return the submission seed data.
     *
     * @return array<int, array{title: string, status: int, daysAgo: int}>
     */
    private function submissionData(): array
    {
        return [
            // Needs action
            [
                'title' => 'Computational Literary Analysis',
                'status' => Submission::INITIALLY_SUBMITTED,
                'daysAgo' => 3,
            ],
            [
                'title' => 'ML for Manuscript Dating',
                'status' => Submission::INITIALLY_SUBMITTED,
                'daysAgo' => 7,
            ],
            [
                'title' => 'Corpus Analysis of Early Texts',
                'status' => Submission::RESUBMITTED,
                'daysAgo' => 2,
            ],
            [
                'title' => 'Digital Preservation of Oral Histories',
                'status' => Submission::AWAITING_DECISION,
                'daysAgo' => 14,
            ],
            [
                'title' => 'Citation Network Analysis',
                'status' => Submission::AWAITING_DECISION,
                'daysAgo' => 10,
            ],
            [
                'title' => 'GIS Mapping of Trade Routes',
                'status' => Submission::EXPIRED,
                'daysAgo' => 45,
            ],
            [
                'title' => 'NLP for Ancient Languages',
                'status' => Submission::INITIALLY_SUBMITTED,
                'daysAgo' => 1,
            ],
            [
                'title' => 'Digital Storytelling and Archives',
                'status' => Submission::RESUBMITTED,
                'daysAgo' => 6,
            ],

            // In progress
            [
                'title' => 'Topic Modeling in Periodicals',
                'status' => Submission::AWAITING_REVIEW,
                'daysAgo' => 5,
            ],
            [
                'title' => 'Stylometric Authorship Attribution',
                'status' => Submission::UNDER_REVIEW,
                'daysAgo' => 12,
            ],
            [
                'title' => 'Sentiment in Historical Letters',
                'status' => Submission::UNDER_REVIEW,
                'daysAgo' => 20,
            ],
            [
                'title' => 'OCR for Degraded Documents',
                'status' => Submission::UNDER_REVIEW,
                'daysAgo' => 8,
            ],
            [
                'title' => 'Crowdsourced Transcription Quality',
                'status' => Submission::AWAITING_REVIEW,
                'daysAgo' => 1,
            ],
            [
                'title' => 'Semantic Web for Library Catalogs',
                'status' => Submission::UNDER_REVIEW,
                'daysAgo' => 15,
            ],
            [
                'title' => 'Census Data Visualization',
                'status' => Submission::AWAITING_REVIEW,
                'daysAgo' => 4,
            ],
            [
                'title' => 'Algorithmic Bias in Heritage Systems',
                'status' => Submission::UNDER_REVIEW,
                'daysAgo' => 22,
            ],
            [
                'title' => 'Pedagogy of DH Methods',
                'status' => Submission::AWAITING_DECISION,
                'daysAgo' => 9,
            ],

            // Awaiting author
            [
                'title' => 'Linked Open Data for Museums',
                'status' => Submission::RESUBMISSION_REQUESTED,
                'daysAgo' => 30,
            ],
            [
                'title' => 'TEI Best Practices',
                'status' => Submission::REVISION_REQUESTED,
                'daysAgo' => 25,
            ],
            [
                'title' => 'VR in Archaeological Reconstruction',
                'status' => Submission::RESUBMISSION_REQUESTED,
                'daysAgo' => 18,
            ],

            // Drafts (hidden from dashboard)
            [
                'title' => 'Notes on Digital Paleography',
                'status' => Submission::DRAFT,
                'daysAgo' => 1,
            ],
            [
                'title' => 'Draft: Computational Philology',
                'status' => Submission::DRAFT,
                'daysAgo' => 5,
            ],

            // Completed
            [
                'title' => 'NER in Archival Records',
                'status' => Submission::ACCEPTED_AS_FINAL,
                'daysAgo' => 60,
            ],
            [
                'title' => 'Digital Edition Workflows',
                'status' => Submission::ACCEPTED_AS_FINAL,
                'daysAgo' => 90,
            ],
            [
                'title' => 'Audio Analysis of Sound Recordings',
                'status' => Submission::ACCEPTED_AS_FINAL,
                'daysAgo' => 35,
            ],
            [
                'title' => 'Blockchain for Heritage Provenance',
                'status' => Submission::REJECTED,
                'daysAgo' => 40,
            ],
            [
                'title' => 'Automated Handwriting Analysis',
                'status' => Submission::REJECTED,
                'daysAgo' => 55,
            ],
            [
                'title' => 'Enlightenment Social Networks',
                'status' => Submission::ARCHIVED,
                'daysAgo' => 120,
            ],
            [
                'title' => 'Medieval Settlement Analysis',
                'status' => Submission::ARCHIVED,
                'daysAgo' => 150,
            ],
            [
                'title' => 'Historical Photo Classification',
                'status' => Submission::DELETED,
                'daysAgo' => 180,
            ],
        ];
    }
}
