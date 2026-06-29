<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\AvatarReport;
use App\Models\User;
use GraphQL\Error\Error;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ReportUserAvatar
{
    /**
     * Max new reports a single reporter may file per hour, to blunt volume
     * abuse. Idempotent re-reports of an already-pending image don't count.
     */
    private const MAX_REPORTS_PER_HOUR = 15;

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

        // Idempotent per (reporter, reported media): re-reporting the same
        // image returns the existing pending report, but if the user has since
        // swapped to a different avatar that new media is reportable in its own
        // right rather than silently folded into the stale report.
        $existing = $this->pendingReport($reportedUser, $reporter, $reportedMedia);
        if ($existing !== null) {
            return $existing;
        }

        $rateKey = 'avatar-report:' . $reporter->id;
        if (RateLimiter::tooManyAttempts($rateKey, self::MAX_REPORTS_PER_HOUR)) {
            throw new Error('You are reporting too frequently. Please slow down and try again later.');
        }

        // Pin the exact media that was reported so a later avatar change can't
        // make review ambiguous or cause the wrong image to be removed.
        try {
            $report = AvatarReport::create([
                'user_id' => $reportedUser->id,
                'media_id' => $reportedMedia->id,
                'reported_media_uuid' => $reportedMedia->uuid,
                'reporter_user_id' => $reporter->id,
                'reason' => $args['reason'] ?? null,
            ]);
        } catch (QueryException $e) {
            // Lost a race: a concurrent request created the pending report first
            // and tripped the pending-dedup unique index. Return that report
            // rather than surfacing a constraint error.
            $winner = $this->pendingReport($reportedUser, $reporter, $reportedMedia);
            if ($winner !== null) {
                return $winner;
            }
            throw $e;
        }

        RateLimiter::hit($rateKey, 3600);

        // Copy the reported image into a private, moderator-only collection so
        // the evidence survives the user swapping or deleting their avatar.
        $reportedMedia->copy(
            $report,
            AvatarReport::SNAPSHOT_COLLECTION,
            AvatarReport::SNAPSHOT_DISK
        );

        return $report;
    }

    /**
     * The reporter's pending report against this exact media, if one exists.
     */
    private function pendingReport(
        User $reportedUser,
        User $reporter,
        Media $reportedMedia
    ): ?AvatarReport {
        return AvatarReport::where('user_id', $reportedUser->id)
            ->where('reporter_user_id', $reporter->id)
            ->where('media_id', $reportedMedia->id)
            ->first();
    }
}
