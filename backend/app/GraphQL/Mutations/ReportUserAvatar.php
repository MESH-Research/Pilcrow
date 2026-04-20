<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\AvatarReport;
use App\Models\User;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

class ReportUserAvatar
{
    /**
     * File (or return an existing pending) report against a user's avatar.
     *
     * @param array{userId: string, reason: ?string} $args
     */
    public function __invoke(null $_, array $args): AvatarReport
    {
        /** @var \App\Models\User|null $reporter */
        $reporter = Auth::user();
        if ($reporter === null) {
            throw new Error('Authentication required.');
        }

        $reportedUser = User::findOrFail($args['userId']);

        if ((string)$reportedUser->id === (string)$reporter->id) {
            throw new Error('You cannot report your own avatar.');
        }

        if ($reportedUser->getAvatarMedia() === null) {
            throw new Error('This user does not have an uploaded avatar to report.');
        }

        // Idempotent: one pending report per (reporter, reported) pair.
        $existing = AvatarReport::where('user_id', $reportedUser->id)
            ->where('reporter_user_id', $reporter->id)
            ->where('status', AvatarReport::STATUS_PENDING)
            ->first();
        if ($existing !== null) {
            return $existing;
        }

        return AvatarReport::create([
            'user_id' => $reportedUser->id,
            'reporter_user_id' => $reporter->id,
            'reason' => $args['reason'] ?? null,
            'status' => AvatarReport::STATUS_PENDING,
        ]);
    }
}
