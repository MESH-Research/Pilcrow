<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AvatarReport extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const STATUS_PENDING = 'pending';
    public const STATUS_DISMISSED = 'dismissed';
    public const STATUS_REMOVED = 'removed';

    /**
     * Private, moderator-only collection holding a snapshot copy of the exact
     * avatar that was reported. Captured at report time so review survives the
     * user later swapping or deleting their avatar; never publicly served.
     * Purged the moment the report is resolved (any outcome) — we never retain
     * violative content past the moderation decision.
     */
    public const SNAPSHOT_COLLECTION = 'reported_avatar';
    public const SNAPSHOT_DISK = 'media_private';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'media_id',
        'reported_media_uuid',
        'reporter_user_id',
        'reason',
        'status',
        'resolved_by_user_id',
        'resolved_at',
        'resolution_notes',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Register the private snapshot collection. Single-file: one report pins
     * one reported image. Stored on the non-public media_private disk.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::SNAPSHOT_COLLECTION)
            ->singleFile()
            ->useDisk(self::SNAPSHOT_DISK);
    }

    /**
     * The user whose avatar was reported.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The exact media row that was reported. Null once that media has been
     * deleted (e.g. the user replaced their avatar after the report).
     */
    public function reportedMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    /**
     * The user who filed the report (nullable — reporter may have been
     * deleted since).
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    /**
     * The administrator who resolved the report.
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    /**
     * The retained private snapshot of the reported avatar, if still held.
     */
    public function getSnapshotMedia(): ?Media
    {
        return $this->getFirstMedia(self::SNAPSHOT_COLLECTION);
    }

    /**
     * Resolve the GraphQL `reported_avatar_url` field: an ability-gated URL to
     * the retained snapshot of the exact image that was reported, or null once
     * the snapshot has been purged (or was never captured). The URL points at
     * a controller that re-checks the admin_avatar_moderate ability per request — the
     * snapshot is never publicly addressable.
     */
    public function getReportedAvatarUrl(): ?string
    {
        if ($this->getSnapshotMedia() === null) {
            return null;
        }

        return route('avatar-report.snapshot', ['avatarReport' => $this->id]);
    }
}
