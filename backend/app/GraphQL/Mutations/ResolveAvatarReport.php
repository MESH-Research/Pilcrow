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

        if ($removeAvatar) {
            $report->user->clearMediaCollection(User::AVATAR_COLLECTION);

            if (!empty($args['blockFutureUploads'])) {
                $report->user->setModerationFlag(ModerationFlag::AvatarUploadBlocked);
            }

            // Close out other pending reports against the same user — the
            // avatar is gone, so they no longer need moderator attention.
            AvatarReport::where('user_id', $report->user_id)
                ->where('status', AvatarReport::STATUS_PENDING)
                ->where('id', '!=', $report->id)
                ->update([
                    'status' => AvatarReport::STATUS_REMOVED,
                    'resolved_by_user_id' => $admin->id,
                    'resolved_at' => Carbon::now(),
                    'resolution_notes' => 'Closed automatically when avatar was removed.',
                ]);
        }

        $report->fill([
            'status' => $status,
            'resolved_by_user_id' => $admin->id,
            'resolved_at' => Carbon::now(),
            'resolution_notes' => $args['notes'] ?? null,
        ])->save();

        return $report->refresh();
    }
}
