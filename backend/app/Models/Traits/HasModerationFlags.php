<?php
declare(strict_types=1);

namespace App\Models\Traits;

use App\Enums\ModerationFlag;

/**
 * Per-model moderation state, stored as a flat array of active flag keys in a
 * `moderation_flags` column (mirrors the feature_opt_ins pattern). Presence of
 * a key means the flag is set; absence means it is not.
 *
 * This is deliberately NOT authorization: it is moderation data on the record,
 * kept out of the Bouncer ability graph so "what a user is allowed to do" and
 * "what a moderator has done to this record" never get confused. See
 * {@see \App\Enums\ModerationFlag}.
 *
 * Consuming models must declare the backing column as an array cast, e.g.
 *   protected $casts = ['moderation_flags' => 'array'];
 *
 * @property array<int, string>|null $moderation_flags
 */
trait HasModerationFlags
{
    /**
     * Whether the given moderation flag is set on this record.
     */
    public function hasModerationFlag(ModerationFlag $flag): bool
    {
        return in_array($flag->value, $this->moderation_flags ?? [], true);
    }

    /**
     * Set a moderation flag. Idempotent. Persists immediately.
     */
    public function setModerationFlag(ModerationFlag $flag): void
    {
        $flags = $this->moderation_flags ?? [];
        if (!in_array($flag->value, $flags, true)) {
            $flags[] = $flag->value;
            $this->moderation_flags = array_values($flags);
            $this->save();
        }
    }

    /**
     * Clear a moderation flag. Idempotent. Persists immediately.
     */
    public function clearModerationFlag(ModerationFlag $flag): void
    {
        $flags = $this->moderation_flags ?? [];
        $filtered = array_values(array_filter(
            $flags,
            fn($value) => $value !== $flag->value
        ));
        if ($filtered !== $flags) {
            $this->moderation_flags = $filtered;
            $this->save();
        }
    }
}
