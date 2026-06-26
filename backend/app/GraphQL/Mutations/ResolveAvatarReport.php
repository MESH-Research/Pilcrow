<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Enums\ModerationFlag;
use App\Models\AvatarReport;
use App\Models\User;
use GraphQL\Error\Error;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ResolveAvatarReport
{
    /**
     * Dismiss a report without removing the avatar.
     *
     * @param array{id: string, notes: ?string} $args
     */
    public function dismiss(null $_, array $args): AvatarReport
    {
        return $this->resolve($args, AvatarReport::STATUS_DISMISSED, false);
    }

    /**
     * Resolve a report and remove the reported user's avatar.
     *
     * @param array{id: string, notes: ?string, blockFutureUploads?: ?bool} $args
     */
    public function resolveAndRemove(null $_, array $args): AvatarReport
    {
        return $this->resolve($args, AvatarReport::STATUS_REMOVED, true);
    }

    /**
     * @param array{
     *     id: string,
     *     notes: ?string,
     *     blockFutureUploads?: ?bool
     * } $args
     */
    private function resolve(array $args, string $status, bool $removeAvatar): AvatarReport
    {
        /** @var \App\Models\User|null $admin */
        $admin = Auth::user();
        if ($admin === null) {
            throw new Error('Authentication required.');
        }

        /** @var \App\Models\AvatarReport $report */
        $report = AvatarReport::findOrFail($args['id']);

        if ($report->status !== AvatarReport::STATUS_PENDING) {
            throw new Error('This report has already been resolved.');
        }

        $now = Carbon::now();
        $notes = $args['notes'] ?? null;

        if ($removeAvatar) {
            // Only remove the current avatar if it is still the exact image
            // that was reported. If the user swapped or deleted it after the
            // report, removing would delete an innocent replacement — skip it
            // and note the staleness. Compare by the durable reported UUID
            // (media_id is nulled the moment the old media row is deleted, so it
            // can't be trusted here). When the UUID is absent (e.g. a legacy
            // report) we can't compare, so fall back to removing the current
            // avatar.
            $currentMedia = $report->user->getAvatarMedia();
            $reportedUuid = $report->reported_media_uuid;
            $isStale = $reportedUuid !== null
                && ($currentMedia === null
                    || $currentMedia->uuid !== $reportedUuid);

            if ($isStale) {
                $staleNote = 'Reported avatar was already changed or removed before review; '
                    . 'current avatar left intact.';
                $notes = $notes ? ($notes . ' ' . $staleNote) : $staleNote;
            } else {
                $report->user->clearMediaCollection(User::AVATAR_COLLECTION);
            }

            if (!empty($args['blockFutureUploads'])) {
                $report->user->setModerationFlag(ModerationFlag::AvatarUploadBlocked);
            }

            // Retain the private snapshot for the appeals / repeat-offender
            // window, after which the scheduled purge command deletes it.
            $report->purge_after = $now->copy()
                ->addDays(AvatarReport::SNAPSHOT_RETENTION_DAYS);

            // Close out other pending reports against the same user — the
            // avatar is gone, so they no longer need moderator attention. Their
            // retained snapshots get the same purge deadline.
            AvatarReport::where('user_id', $report->user_id)
                ->where('status', AvatarReport::STATUS_PENDING)
                ->where('id', '!=', $report->id)
                ->update([
                    'status' => AvatarReport::STATUS_REMOVED,
                    'resolved_by_user_id' => $admin->id,
                    'resolved_at' => $now,
                    'purge_after' => $now->copy()
                        ->addDays(AvatarReport::SNAPSHOT_RETENTION_DAYS),
                    'resolution_notes' => 'Closed automatically when avatar was removed.',
                ]);
        }

        $report->fill([
            'status' => $status,
            'resolved_by_user_id' => $admin->id,
            'resolved_at' => $now,
            'resolution_notes' => $notes,
        ])->save();

        // Dismissals are not actionable content — purge the snapshot now rather
        // than holding a flagged image with no moderation reason to keep it.
        if ($status === AvatarReport::STATUS_DISMISSED) {
            $report->clearMediaCollection(AvatarReport::SNAPSHOT_COLLECTION);
        }

        return $report->refresh();
    }
}
