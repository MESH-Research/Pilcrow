<?php
declare(strict_types=1);

namespace App\Enums;

/**
 * The catalog of per-user MODERATION flags.
 *
 * These describe moderation state stored directly on the user (in the
 * `moderation_flags` JSON column), NOT authorization. They are deliberately
 * kept separate from {@see \App\Auth\Abilities\GlobalAbility} and the Bouncer
 * ability graph so that "what a user is allowed to do" (abilities) never gets
 * confused with "what a moderator has done to this user" (flags). Add a new
 * case here to store another flag of this kind — no schema change required.
 *
 * The backing value is the key persisted in the JSON array; presence of the
 * key means the flag is set.
 */
enum ModerationFlag: string
{
    case AvatarUploadBlocked = 'avatar_upload_blocked';
}
