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
    protected $defaultOptions = [
        'replies' => 0,
        'highlight' => null,
    ];

    /**
     * Run the seeder
     *
     * @return void
     */
    public function run()
    {
        $this->callOnce(SubmissionSeeder::class);
        InlineComment::withoutEvents(function () {
            $this->create(100, 1, ['replies' => 1, 'highlight' => [30, 80]], [2]);
            $this->create(100, 3, ['highlight' => [430, 445]]);
            $this->create(100, 5, ['replies' => 10, 'highlight' => [630, 720]]);

            // Export preview submission (113) — comments for testing the export system
            $this->create(113, 5, ['replies' => 3, 'highlight' => [20, 90]]);
            $this->create(113, 4, ['replies' => 2, 'highlight' => [200, 260]], [5]);
            $deletedInline = $this->create(113, 6, ['highlight' => [400, 450]]);
            $deletedInline->delete();
            $this->create(113, 3, ['replies' => 1, 'highlight' => [500, 560]], [5]);
        });
    }

    /**
     * Run the database seeds.
     *
     * @param int $submissionId
     * @param int $userId
     * @param array $options
     * @param array $replyIds
     * @return \App\Models\InlineComment
     */
    public function create($submissionId, $userId, $options = [], $replyIds = null)
    {
        $opts = array_merge($this->defaultOptions, $options);
        $userIds = User::all()->pluck('id');
        $style_criterias = StyleCriteria::inRandomOrder()
            ->limit(rand(1, 4))
            ->get()
            ->toArray();

        $submission = Submission::find($submissionId);
        $contentLength = mb_strlen($submission->content->data);

        if (is_array($opts['highlight'])) {
            [$from, $to] = $opts['highlight'];
        } else {
            $from = rand(0, $contentLength);
            $length = rand(13, 150);
            $to = $from + $length > $contentLength ? $contentLength : $from + $length;
        }
        $parent = InlineComment::factory()->create([
            'submission_id' => $submissionId,
            'created_by' => $userId,
            'updated_by' => $userId,
            'style_criteria' => $style_criterias,
            'from' => $from,
            'to' => $to,
        ]);

        // Replies
        if ($opts['replies'] > 0) {
            // Seed inline comment replies
            $comments = collect([$parent]);
            for ($i = 0; $i < $opts['replies']; $i++) {
                if ($replyIds != null) {
                    $replyId = $replyIds[$i % count($replyIds)];
                } else {
                    $replyId = $userIds->random();
                }
                $reply = $this->createCommentReply($submissionId, $replyId, $parent, $comments->random());
                $comments->push($reply);
            }
        }

        return $parent;
    }

    /**
     * @param int $submissionId
     * @param \App\Models\User $userId
     * @param \App\Models\InlineComment $parent
     * @param \App\Models\InlineComment $reply_to
     * @return \App\Models\InlineComment
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
