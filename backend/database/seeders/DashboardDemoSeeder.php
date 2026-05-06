<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Publication;
use App\Models\StyleCriteria;
use App\Models\Submission;
use App\Models\SubmissionContent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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

        // Give most of the pooled users a populated profile so the
        // publication admin's user-detail pages have something real
        // to show off. A few are left blank to exercise the empty
        // state in `UserProfileCard`.
        $this->seedProfileMetadata($submitterPool, 0.85);
        $this->seedProfileMetadata($coordinatorPool, 1.0);
        $this->seedProfileMetadata($reviewerPool, 0.8);

        // A couple of staged (invited-but-not-accepted) reviewers so the
        // dashboard Review Team tab shows the "Invited" badge in action.
        $invitedReviewers = collect([
            User::createStagedUser('invited.reviewer@example.com'),
            User::createStagedUser('pending.peer@example.com'),
        ]);

        // Attach each invited reviewer to one in-progress submission so
        // they surface in both the submission details and the Review Team
        // tab of Publication > Manage > Users.
        $invitationTargets = array_rand(
            array_filter(
                $submissions,
                fn($s) => $s['status'] === Submission::UNDER_REVIEW
                    || $s['status'] === Submission::AWAITING_REVIEW
            ),
            min(
                $invitedReviewers->count(),
                count(
                    array_filter(
                        $submissions,
                        fn($s) => $s['status'] === Submission::UNDER_REVIEW
                            || $s['status'] === Submission::AWAITING_REVIEW
                    )
                )
            )
        );
        if (! is_array($invitationTargets)) {
            $invitationTargets = [$invitationTargets];
        }
        // Flag the selected submissions so the main loop knows to attach
        // an invited reviewer (paired with the invitedReviewers pool).
        foreach ($invitationTargets as $i => $index) {
            $submissions[$index]['invitedReviewer'] = $invitedReviewers[$i];
        }

        foreach ($submissions as $data) {
            $submitter = $submitterPool->random();

            // Always seed at least a couple of content-history rows so
            // the previewer / reviewer pages have something to render.
            // Without an attached SubmissionContent, the legacy preview
            // page crashes because it assumes content is non-null.
            $factory = Submission::factory()
                ->for($publication)
                ->has(SubmissionContent::factory()->count(2), 'contentHistory')
                ->hasAttached($submitter, [], 'submitters');

            if (empty($data['noCoordinator'])) {
                $coordinator = $coordinatorPool->random();
                $factory = $factory->hasAttached(
                    $coordinator,
                    [],
                    'reviewCoordinators'
                );
            }

            if (empty($data['noReviewers'])) {
                foreach ($reviewerPool->random(random_int(1, 4)) as $reviewer) {
                    $factory = $factory->hasAttached(
                        $reviewer,
                        [],
                        'reviewers'
                    );
                }
            }

            if (! empty($data['invitedReviewer'])) {
                $factory = $factory->hasAttached(
                    $data['invitedReviewer'],
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

            $submission = $factory->create([
                'title' => $data['title'],
                'status' => $data['status'],
                'created_by' => $submitter->id,
                'updated_by' => $submitter->id,
                'created_at' => $created,
                'updated_at' => $updated,
            ]);

            // Point the submission at the most recent content-history
            // entry so `submission.content` is non-null.
            $submission->content()->associate($submission->contentHistory->last());
            $submission->save();

            $this->synthesizeSubmittedAudit($submission, $submitter->id);
        }

        // Supplemental publications assigned to the editor so the
        // Manage dashboard has a variety of rows — each with a
        // deliberately lopsided status distribution so different
        // stage columns show zero on different rows.
        $this->seedSupplementalPublications($editor, $submitterPool);
    }

    /**
     * Spin up a handful of extra publications so the Manage dashboard
     * has rows with varied (and sometimes empty) counts across the
     * stage columns. Each publication is assigned to the given editor
     * so they show up in that user's Manage list; the `$statusMix`
     * map drives how many submissions land in each status.
     *
     * @param \App\Models\User $editor
     * @param \Illuminate\Support\Collection<int, \App\Models\User> $submitterPool
     * @return void
     */
    private function seedSupplementalPublications($editor, $submitterPool): void
    {
        $sets = [
            [
                'name' => 'B - Early Access Studies',
                'statusMix' => [
                    Submission::INITIALLY_SUBMITTED => 4,
                    Submission::RESUBMITTED => 2,
                ],
            ],
            [
                'name' => 'C - Long-form Reviews',
                'statusMix' => [
                    Submission::AWAITING_REVIEW => 2,
                    Submission::UNDER_REVIEW => 3,
                    Submission::AWAITING_DECISION => 1,
                ],
            ],
            [
                'name' => 'D - Retired Review Archive',
                'statusMix' => [
                    Submission::ACCEPTED_AS_FINAL => 5,
                    Submission::REJECTED => 3,
                    Submission::EXPIRED => 1,
                    Submission::ARCHIVED => 2,
                ],
            ],
            [
                'name' => 'E - Decision Desk Quarterly',
                'statusMix' => [
                    Submission::AWAITING_DECISION => 4,
                ],
            ],
        ];

        foreach ($sets as $set) {
            $publication = Publication::factory()
                ->hasAttached($editor, [], 'editors')
                ->create([
                    'name' => $set['name'],
                    'is_accepting_submissions' => true,
                ]);

            foreach ($set['statusMix'] as $status => $count) {
                for ($i = 0; $i < $count; $i++) {
                    $submitter = $submitterPool->random();
                    $submission = Submission::factory()
                        ->for($publication)
                        ->has(SubmissionContent::factory()->count(2), 'contentHistory')
                        ->hasAttached($submitter, [], 'submitters')
                        ->create([
                            'title' => $set['name'] . ' submission ' . ($i + 1),
                            'status' => $status,
                            'created_by' => $submitter->id,
                            'updated_by' => $submitter->id,
                        ]);
                    $submission->content()->associate(
                        $submission->contentHistory->last()
                    );
                    $submission->save();
                    $this->synthesizeSubmittedAudit($submission, $submitter->id);
                }
            }
        }
    }

    /**
     * Synthesize the DRAFT->INITIALLY_SUBMITTED audit row that
     * Submission::getSubmittedAt() reads. Demo submissions are created
     * at their target status directly (no transition), so without this
     * row the Overview panel and dashboard "Submitted" column would be
     * null. Skips drafts since they haven't been submitted.
     *
     * @param \App\Models\Submission $submission
     * @param int $submitterId
     * @return void
     */
    private function synthesizeSubmittedAudit(
        Submission $submission,
        int $submitterId
    ): void {
        if ($submission->status === Submission::DRAFT) {
            return;
        }

        DB::table('audits')->insert([
            'user_type' => User::class,
            'user_id' => $submitterId,
            'event' => 'updated',
            'auditable_type' => Submission::class,
            'auditable_id' => $submission->id,
            'old_values' => json_encode(['status' => Submission::DRAFT]),
            'new_values' => json_encode(['status' => Submission::INITIALLY_SUBMITTED]),
            'created_at' => $submission->created_at,
            'updated_at' => $submission->created_at,
        ]);
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
                // Triaged to a coordinator but reviewers not yet assigned.
                'title' => 'Computational Literary Analysis',
                'status' => Submission::INITIALLY_SUBMITTED,
                'daysAgo' => 3,
                'noReviewers' => true,
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
                // Just arrived — no coordinator or reviewers assigned yet.
                'title' => 'NLP for Ancient Languages',
                'status' => Submission::INITIALLY_SUBMITTED,
                'daysAgo' => 1,
                'noCoordinator' => true,
                'noReviewers' => true,
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
                // Coordinator accepted for review but hasn't picked reviewers.
                'title' => 'Crowdsourced Transcription Quality',
                'status' => Submission::AWAITING_REVIEW,
                'daysAgo' => 1,
                'noReviewers' => true,
            ],
            [
                'title' => 'Semantic Web for Library Catalogs',
                'status' => Submission::UNDER_REVIEW,
                'daysAgo' => 15,
            ],
            [
                // Reviewers were assigned but the coordinator stepped away;
                // the submission needs a new coordinator.
                'title' => 'Census Data Visualization',
                'status' => Submission::AWAITING_REVIEW,
                'daysAgo' => 4,
                'noCoordinator' => true,
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

            // --- UI stress tests: exceptionally long titles -------------
            // These exist so we can eyeball how title-prefixed icons,
            // grid cards, list rows, and breadcrumb-style detail pages
            // cope with titles that break whitespace assumptions.
            [
                // Long but single-line; exercises title ellipsis in
                // narrow grid cards and tight table cells.
                'title' =>
                    'A Multi-Modal Transformer Architecture for Cross-Linguistic '
                    . 'Attribution of Anonymously Authored Eighteenth-Century '
                    . 'Pamphlets Across Disparate Archival Repositories',
                'status' => Submission::UNDER_REVIEW,
                'daysAgo' => 11,
            ],
            [
                // Contains a single very long unbroken word (URL-like)
                // that can't wrap — stresses word-break handling.
                'title' =>
                    'Corpus Link Integrity: '
                    . 'https://example-archive.institution.edu/collections/dh/'
                    . 'longitudinal-studies-of-literary-style-across-centuries/'
                    . 'dataset-catalog-2026?revision=final&notes=see-appendix',
                'status' => Submission::AWAITING_DECISION,
                'daysAgo' => 6,
                'noReviewers' => true,
            ],
            [
                // Sentence-style title with punctuation that the card
                // layout should wrap gracefully into multiple lines.
                'title' =>
                    'Re-Evaluating Stylometric Attribution Methods, Including '
                    . '"Delta," "Zeta," and n-Gram Variants, When Applied to '
                    . 'Translated Works Whose Original Language Used Non-Latin '
                    . 'Orthography and Where the Translator\'s Voice May '
                    . 'Dominate the Corpus',
                'status' => Submission::REVISION_REQUESTED,
                'daysAgo' => 18,
            ],
        ];
    }

    /**
     * Populate `profile_metadata` on each given user with a realistic
     * mix of fields. `$coverage` (0..1) controls how often a user
     * gets any profile at all — the remainder are left blank to
     * exercise the empty state in the UI.
     *
     * The shape matches the ProfileMetadata GraphQL type and
     * `UserProfileCard.vue`'s expectations: position_title,
     * specialization, affiliation, biography, websites[],
     * social_media{...}, academic_profiles{...}.
     *
     * @param \Illuminate\Support\Collection<int, \App\Models\User> $users
     * @param float $coverage
     * @return void
     */
    private function seedProfileMetadata($users, float $coverage = 0.8): void
    {
        $faker = fake();

        $positions = [
            'Associate Professor',
            'Assistant Professor',
            'Lecturer',
            'Postdoctoral Fellow',
            'Senior Researcher',
            'Research Scientist',
            'Independent Scholar',
            'Librarian',
        ];
        $specializations = [
            'Digital humanities, text encoding',
            'Medieval literature and manuscript studies',
            'Sociolinguistics of the early web',
            'Cultural analytics and distant reading',
            'Critical editing in open-access publishing',
            'Stylometry and computational authorship',
            'Archival studies and oral history',
            'Comparative poetics',
        ];

        foreach ($users as $user) {
            if ($faker->boolean((int)($coverage * 100)) === false) {
                continue;
            }

            // Biography — roughly 20% of profiles get a long one that
            // will trip the "Read more" control.
            $biography = $faker->boolean(20)
                ? $faker->paragraphs(3, asText: true)
                : $faker->sentence(mt_rand(12, 24));

            // Websites — 0..2 extras per profile.
            $websites = [];
            $websiteNum = mt_rand(0, 2);
            for ($i = 0; $i < $websiteNum; $i++) {
                $websites[] = $faker->url();
            }

            $metadata = [
                'position_title' => $faker->boolean(90)
                    ? $faker->randomElement($positions)
                    : null,
                'specialization' => $faker->boolean(75)
                    ? $faker->randomElement($specializations)
                    : null,
                'affiliation' => $faker->boolean(85)
                    ? $faker->company() . ' University'
                    : null,
                'biography' => $faker->boolean(70) ? $biography : null,
                'websites' => $websites,
                'social_media' => [
                    'twitter' => $faker->boolean(40)
                        ? strtolower($faker->firstName() . $faker->randomDigit())
                        : null,
                    'linkedin' => $faker->boolean(50)
                        ? 'linkedin.com/in/' . strtolower(
                            $faker->userName()
                        )
                        : null,
                    'facebook' => null,
                    'instagram' => $faker->boolean(15)
                        ? strtolower($faker->userName())
                        : null,
                    'google' => $faker->boolean(25)
                        ? 'scholar.google.com/citations?user='
                            . $faker->bothify('??###???')
                        : null,
                ],
                'academic_profiles' => [
                    'orcid_id' => $faker->boolean(60)
                        ? $this->fakeOrcid($faker)
                        : null,
                    'humanities_commons' => $faker->boolean(25)
                        ? strtolower($faker->userName())
                        : null,
                ],
            ];

            $user->profile_metadata = $metadata;
            $user->save();
        }
    }

    /**
     * Generate a syntactically-valid-looking ORCID iD (16 digits in
     * four groups, separated by hyphens). The check digit isn't a
     * real ORCID check — seed data only, not a live identifier.
     *
     * @param \Faker\Generator $faker
     * @return string
     */
    private function fakeOrcid($faker): string
    {
        $digits = $faker->numerify('################');

        return substr($digits, 0, 4) . '-' . substr($digits, 4, 4)
            . '-' . substr($digits, 8, 4) . '-' . substr($digits, 12, 4);
    }
}
