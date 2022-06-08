<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\InlineComment;
use App\Models\StyleCriteria;
use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class InlineCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param int $submissionId
     * @param int $replies
     * @return void
     */
    public function run($submissionId, $replies = 0, $highlightRange = [15, 120])
    {
        $userIds = User::all()->pluck('id');
        $userId = $userIds->random();
        $style_criterias = StyleCriteria::inRandomOrder()
            ->limit(rand(1, 4))
            ->get()
            ->toArray();

        $submission = Submission::find($submissionId);
        $contentLength = mb_strlen($submission->content->data);
        $from = rand(0, $contentLength);
        $length = rand(...$highlightRange);
        $to = $from + $length > $contentLength ? $contentLength : $from + $length;
        $parent = InlineComment::factory()->create([
            'submission_id' => $submissionId,
            'created_by' => $userId,
            'updated_by' => $userId,
            'style_criteria' => $style_criterias,
            'from' => $from,
            'to' => $to,
        ]);

        // Replies
        if ($replies > 0) {
            // Seed inline comment replies
            $comments = collect([$parent]);
            for ($i = 0; $i < $replies; $i++) {
                $comments->push($this->createCommentReply(true, $userIds->random(), $parent, $comments->random()));
            }
        }
    }

    /**
     * @param bool $submissionId
     * @param \App\Models\User $userId
     * @param \App\Models\InlineComment|\App\Models\OverallComment $parent
     * @param \App\Models\InlineComment|\App\Models\OverallComment $reply_to
     * @return \App\Models\InlineComment|\App\Models\OverallComment
     */
    private function createCommentReply($submissionId, $userId, $parent, $reply_to)
    {
        $faker = Faker::create();
        $time = Carbon::parse($reply_to->created_at);
        $datetime = $faker->dateTimeBetween($time, Carbon::now());

        return InlineComment::factory()->create([
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
