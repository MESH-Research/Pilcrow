<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\InlineComment;
use App\Models\OverallComment;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class SubmissionCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param int $reply_count
     * @param int $reply_reply_count
     * @return void
     */
    public function run($reply_count = 0, $reply_reply_count = 0)
    {
        $user = User::where('username', 'regularUser')->firstOrFail();
        $inline_parent = InlineComment::factory()->create([
            'submission_id' => 100,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
        $overall_parent = OverallComment::factory()->create([
            'submission_id' => 100,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
        if ($reply_count > 0) {
            // Seed inline comment replies
            for ($i = $reply_count; $i > 0; $i--) {
                $inline_reply = $this->createCommentReply(true, $user, $inline_parent);
            }
            for ($i = $reply_reply_count; $i > 0; $i--) {
                $this->createCommentReply(true, $user, $inline_parent, $inline_reply);
            }

            // Seed overall comment replies
            for ($i = $reply_count; $i > 0; $i--) {
                $overall_reply = $this->createCommentReply(false, $user, $overall_parent);
            }
            for ($i = $reply_reply_count; $i > 0; $i--) {
                $this->createCommentReply(false, $user, $overall_parent, $overall_reply);
            }
        }
    }

    /**
     * @param bool $is_inline
     * @param \App\Models\User $user
     * @param \App\Models\InlineComment|\App\Models\OverallComment $parent
     * @param \App\Models\InlineComment|\App\Models\OverallComment $reply_to
     * @return \App\Models\InlineComment|\App\Models\OverallComment
     */
    private function createCommentReply($is_inline, $user, $parent, $reply_to = null)
    {
        $faker = Faker::create();
        $time = $reply_to ? Carbon::parse($reply_to->created_at) : Carbon::parse($parent->created_at);
        $data = [
            'submission_id' => 100,
            'parent' => $parent->id,
            'reply_to' => $reply_to ? $reply_to->id : null,
            'created_at' => $faker->dateTimeBetween($time, $time->addHours(24)),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ];

        return $is_inline ? InlineComment::factory()->create($data) : OverallComment::factory()->create($data);
    }
}
