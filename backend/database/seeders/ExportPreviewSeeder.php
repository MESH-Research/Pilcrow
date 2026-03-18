<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\InlineComment;
use App\Models\OverallComment;
use App\Models\Submission;
use App\Models\SubmissionContent;
use App\Models\StyleCriteria;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

/**
 * Seeds a submission in ACCEPTED_AS_FINAL status with inline and overall
 * comments for previewing and testing the export system.
 *
 * Submission ID: 113 — "Export Preview Submission"
 *
 * Inline comments:
 *   - Thread by reviewer (user 5) with 3 replies
 *   - Thread by review coordinator (user 4) with 2 replies from reviewer
 *   - Standalone comment by publicationEditor (user 3) with 1 reply from reviewer
 *
 * Overall comments:
 *   - Thread by reviewer (user 5) with 3 replies
 *   - Standalone comment by review coordinator (user 4)
 *   - Thread by publicationEditor (user 3) with 2 replies from reviewer & regularUser
 */
class ExportPreviewSeeder extends Seeder
{
    private const SUBMISSION_ID = 113;

    public function run(): void
    {
        $this->callOnce(SubmissionSeeder::class);

        $this->createSubmission();
        $this->seedInlineComments();
        $this->seedOverallComments();
    }

    private function createSubmission(): void
    {
        $submission = Submission::factory()
            ->hasAttached(
                User::firstWhere('username', 'regularUser'),
                [],
                'submitters'
            )
            ->hasAttached(
                User::firstWhere('username', 'reviewCoordinator'),
                [],
                'reviewCoordinators'
            )
            ->hasAttached(
                User::firstWhere('username', 'reviewer'),
                [],
                'reviewers'
            )
            ->has(SubmissionContent::factory()->count(3), 'contentHistory')
            ->create([
                'id' => self::SUBMISSION_ID,
                'title' => 'Export Preview Submission',
                'publication_id' => 1,
                'created_by' => 6,
                'updated_by' => 6,
                'status' => Submission::DRAFT,
            ]);

        $submission->updated_by = 2;
        $submission->content()->associate($submission->contentHistory->last());
        $submission->save();
        $submission->update(['updated_by' => 3, 'status' => Submission::ACCEPTED_AS_FINAL]);
    }

    private function seedInlineComments(): void
    {
        InlineComment::withoutEvents(function () {
            $this->createInlineComment(5, [20, 90], replies: 3);
            $this->createInlineComment(4, [200, 260], replies: 2, replyUserIds: [5]);
            $this->createInlineComment(3, [500, 560], replies: 1, replyUserIds: [5]);
        });
    }

    private function seedOverallComments(): void
    {
        OverallComment::withoutEvents(function () {
            $this->createOverallComment(5, replies: 3);
            $this->createOverallComment(4);
            $this->createOverallComment(3, replies: 2, replyUserIds: [5, 6]);
        });
    }

    private function createInlineComment(
        int $userId,
        array $highlight,
        int $replies = 0,
        ?array $replyUserIds = null,
    ): InlineComment {
        $styleCriteria = StyleCriteria::inRandomOrder()
            ->limit(rand(1, 4))
            ->get()
            ->toArray();

        $parent = InlineComment::factory()->create([
            'submission_id' => self::SUBMISSION_ID,
            'created_by' => $userId,
            'updated_by' => $userId,
            'style_criteria' => $styleCriteria,
            'from' => $highlight[0],
            'to' => $highlight[1],
        ]);

        $this->createReplies($parent, $replies, $replyUserIds);

        return $parent;
    }

    private function createOverallComment(
        int $userId,
        int $replies = 0,
        ?array $replyUserIds = null,
    ): OverallComment {
        $parent = OverallComment::factory()->create([
            'submission_id' => self::SUBMISSION_ID,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        $this->createReplies($parent, $replies, $replyUserIds);

        return $parent;
    }

    private function createReplies(
        InlineComment|OverallComment $parent,
        int $count,
        ?array $replyUserIds,
    ): void {
        if ($count === 0) {
            return;
        }

        $faker = Faker::create();
        $userIds = User::all()->pluck('id');
        $comments = collect([$parent]);

        for ($i = 0; $i < $count; $i++) {
            $replyUserId = $replyUserIds
                ? $replyUserIds[$i % count($replyUserIds)]
                : $userIds->random();

            $replyTo = $comments->random();
            $time = Carbon::parse($replyTo->created_at);
            $datetime = $faker->dateTimeBetween($time, Carbon::now());

            $reply = $parent::factory()->create([
                'submission_id' => self::SUBMISSION_ID,
                'parent_id' => $parent->id,
                'reply_to_id' => $replyTo->id,
                'created_at' => $datetime,
                'updated_at' => $datetime,
                'created_by' => $replyUserId,
                'updated_by' => $replyUserId,
            ]);

            $comments->push($reply);
        }
    }
}
