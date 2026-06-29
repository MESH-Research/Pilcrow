<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Enums\ModerationFlag;
use App\Models\AvatarReport;
use App\Models\User;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

class ResolveAvatarReport
{
    /**
     * Dismiss a report without removing the avatar. Records the decision in the
     * audit log, then deletes the transient report and its snapshot.
     *
     * @param array{id: string, notes: ?string} $args
     */
    public function dismiss(null $_, array $args): User
    {
        [$report, $reportedUser] = $this->load($args);

        $reportedUser->recordModerationAudit('avatar_report_dismissed', [
            'reporter_id' => $report->reporter_user_id,
            'reason' => $report->reason,
            'notes' => $args['notes'] ?? null,
        ]);

        $this->discard($report);

        return $reportedUser->refresh();
    }

    /**
     * Resolve a report and remove the reported user's avatar — unless the user
     * has since swapped to a different image, in which case the report is closed
     * as stale and the (possibly innocent) current avatar is left intact.
     *
     * @param array{id: string, notes: ?string, blockFutureUploads?: ?bool} $args
     */
    public function resolveAndRemove(null $_, array $args): User
    {
        [$report, $reportedUser] = $this->load($args);
        $notes = $args['notes'] ?? null;

        // Only remove the current avatar if it is still the exact image that was
        // reported. Compare by the durable reported UUID (media_id is nulled the
        // moment the old media row is deleted). When the UUID is absent (legacy)
        // we can't compare, so fall back to removing the current avatar.
        $currentMedia = $reportedUser->getAvatarMedia();
        $reportedUuid = $report->reported_media_uuid;
        $isStale = $reportedUuid !== null
            && ($currentMedia === null || $currentMedia->uuid !== $reportedUuid);

        if ($isStale) {
            // Nothing to remove — the reported image is already gone. Record the
            // closure as a (stale) dismissal and leave sibling reports, which may
            // concern the replacement avatar, pending.
            $reportedUser->recordModerationAudit('avatar_report_dismissed', [
                'stale' => true,
                'reporter_id' => $report->reporter_user_id,
                'reason' => $report->reason,
                'notes' => $notes,
            ]);
            $this->discard($report);

            return $reportedUser->refresh();
        }

        $reportedUser->clearMediaCollection(User::AVATAR_COLLECTION);

        $reportedUser->recordModerationAudit('avatar_removed', [
            'reporter_id' => $report->reporter_user_id,
            'reason' => $report->reason,
            'notes' => $notes,
            'reported_media_uuid' => $reportedUuid,
        ]);

        if (!empty($args['blockFutureUploads'])) {
            $reportedUser->setModerationFlag(ModerationFlag::AvatarUploadBlocked);
            $reportedUser->recordModerationAudit('avatar_upload_blocked', [
                'via' => 'avatar_report',
            ]);
        }

        // The avatar is gone, so every pending report against this user — this
        // one and its siblings — is moot. Delete them all (and their snapshots);
        // the single avatar_removed entry above is the durable record.
        $this->discard($report);
        AvatarReport::where('user_id', $reportedUser->id)
            ->get()
            ->each(fn(AvatarReport $sibling) => $this->discard($sibling));

        return $reportedUser->refresh();
    }

    /**
     * Load the report and its reported user, guarding authentication.
     *
     * @param array{id: string} $args
     * @return array{0: \App\Models\AvatarReport, 1: \App\Models\User}
     */
    private function load(array $args): array
    {
        if (Auth::user() === null) {
            throw new Error('Authentication required.');
        }

        /** @var \App\Models\AvatarReport $report */
        $report = AvatarReport::findOrFail($args['id']);

        return [$report, $report->user];
    }

    /**
     * Delete a transient report and its private snapshot. Spatie removes the
     * report's media when the model is deleted.
     */
    private function discard(AvatarReport $report): void
    {
        $report->delete();
    }
}
