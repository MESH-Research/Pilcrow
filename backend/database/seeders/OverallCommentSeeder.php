<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\OverallComment;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class OverallCommentSeeder extends Seeder
{
    /**
     * Run the seeder
     *
     * @return void
     */
    public function run()
    {
        $this->callOnce(SubmissionSeeder::class);

        OverallComment::withoutEvents(function () {
            // Comments on submission 100 (used by review.spec.ts overall comment tests)
            $this->create(100, 0, 2);
            $this->create(100, 1);
            $this->create(100, 8);
            $this->create(100, 2, 1, [2,3]);

            // Duplicate comments on submission 114 (used by inline-comments.spec.ts)
            $this->create(114, 0, 2);
            $this->create(114, 1);
            $this->create(114, 8);
            $this->create(114, 2, 1, [2,3]);
        });
    }

    /**
     * Run the database seeds.
     *
     * @param int $submissionId
     * @param int $replies
     * @param int $userId
     * @param array $replyIds
     * @return void
     */
    protected function create(int $submissionId, $replies = 0, $userId = null, $replyIds = null)
    {
        $userIds = User::all()->pluck('id');
        if ($userId === null) {
            // Ensure that the comment is not created by the application
            // admin to prevent the admin replying to their own comment
            $userId = $userIds->except(1)->random();
        }

        $parent = OverallComment::factory()->create([
            'submission_id' => $submissionId,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        // Seed replies
        if ($replies > 0) {
            $comments = collect([$parent]);

            for ($i = 0; $i < $replies; $i++) {
                if ($replyIds != null) {
                    $replyId = $replyIds[$i % count($replyIds)];
                } else {
                    $replyId = $userIds->except(1)->random();
                }
                $reply = $this->createCommentReply($submissionId, $replyId, $parent, $comments->random());
                $comments->push($reply);
            }
        }
    }

    /**
     * @param \App\Models\User $userId
     * @param \App\Models\OverallComment $parent
     * @param \App\Models\OverallComment $reply_to
     * @return \App\Models\OverallComment
     */
    private function createCommentReply(int $submissionId, $userId, $parent, $reply_to)
    {
        $faker = Faker::create();
        $time = Carbon::parse($reply_to->created_at);
        $datetime = $faker->dateTimeBetween($time, Carbon::now());

        return OverallComment::factory()->create([
            'submission_id' => $submissionId,
            'parent_id' => $parent->id,
            'reply_to_id' => $reply_to->id,
            'created_at' => $datetime,
            'updated_at' => $datetime,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }
}
