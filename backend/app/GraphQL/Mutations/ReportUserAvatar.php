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

        $reportedMedia = $reportedUser->getAvatarMedia();
        if ($reportedMedia === null) {
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

        // Pin the exact media that was reported so a later avatar change can't
        // make review ambiguous or cause the wrong image to be removed.
        $report = AvatarReport::create([
            'user_id' => $reportedUser->id,
            'media_id' => $reportedMedia->id,
            'reported_media_uuid' => $reportedMedia->uuid,
            'reporter_user_id' => $reporter->id,
            'reason' => $args['reason'] ?? null,
            'status' => AvatarReport::STATUS_PENDING,
        ]);

        // Copy the reported image into a private, moderator-only collection so
        // the evidence survives the user swapping or deleting their avatar.
        $reportedMedia->copy(
            $report,
            AvatarReport::SNAPSHOT_COLLECTION,
            AvatarReport::SNAPSHOT_DISK
        );

        return $report;
    }
}
