<?php
declare(strict_types=1);

namespace App\Models;

use App\Auth\Abilities\GlobalAbility;
use App\Auth\Roles\GlobalRole;
use App\Auth\Roles\ScopedRole;
use App\Builders\UserBuilder;
use App\Enums\ModerationFlag;
use App\Models\Traits\HasAvatarImage;
use App\Models\Traits\HasModerationFlags;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Attributes\SearchUsingPrefix;
use Laravel\Scout\Searchable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Events\AuditCustom;
use Silber\Bouncer\Database\HasRolesAndAbilities;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements MustVerifyEmail, HasMedia, Auditable
{
    use HasFactory;
    use Notifiable;
    use HasApiTokens;
    use HasRolesAndAbilities;
    use Searchable;
    use InteractsWithMedia;
    use HasAvatarImage;
    use HasModerationFlags;
    use AuditableTrait;

    /**
     * Don't audit ordinary attribute changes (profile edits, logins). The only
     * User audits we want are explicit moderation custom events emitted via
     * {@see self::recordModerationAudit()}, so the log stays a clean moderation
     * trail rather than churning on every save.
     *
     * @var array<int, string>
     */
    protected $auditEvents = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'profile_metadata',
        'staged',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'profile_metadata' => 'array',
        'beta' => 'boolean',
        'feature_opt_ins' => 'array',
        'moderation_flags' => 'array',
    ];

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \App\Builders\UserBuilder
     */
    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query);
    }

    /**
     * Model booted
     *
     * Clear email_verified_at and trigger a new verification notification if email field is updated.
     *
     * @return void
     */
    public static function booted(): void
    {
        static::updated(function ($model) {
            if ($model->isDirty('email')) {
                $model->sendEmailVerificationNotification();
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('email')) {
                $model->email_verified_at = null;
            }
        });
    }

    /**
     * Lowercase email before saving to persistance.
     *
     * @param string $value
     * @return void
     */
    public function setEmailAttribute(?string $value): void
    {
        $email = $value ? strtolower($value) : $value;
        $this->attributes['email'] = $email;
    }

    /**
     * Returns a hash to use for email verification
     *
     * @param string $expires Timestamp
     * @return string
     */
    public function makeEmailVerificationHash(string $expires): string
    {
        return hash_hmac(
            'sha256',
            "{$this->getKey()}#{$this->getEmailForVerification()}#{$expires}",
            Config::get('app.key')
        );
    }

    /**
     * Return URL for email verification
     *
     * @return string
     */
    public function getEmailVerificationUrl(): string
    {
        $expires = (string)Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60))->timestamp;
        $hash = $this->makeEmailVerificationHash($expires);

        return url("verify-email/{$expires}/{$hash}");
    }

    /**
     * Verify an email verification token.
     *
     * @param string $token Token to validate
     * @param string $expires Supplied expiration for token
     * @return bool
     */
    public function verifyEmailToken(string $token, string $expires): bool
    {
        return hash_equals($this->makeEmailVerificationHash($expires), $token);
    }

    /**
     * Get the indexable data array for the model
     *
     * @return array
     */
    #[SearchUsingPrefix(['email', 'username', 'name', 'id'])]
    public function toSearchableArray()
    {
        return [
            'id' => (int)$this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
        ];
    }

    /**
     * Feature-flag keys the user has opted into, stored as a flat array
     * of enabled keys. An absent key means not opted in (opting out
     * removes the key). Presence of the key is the access grant.
     *
     * @return array<int, string>
     */
    public function getActiveFeatureOptIns(): array
    {
        return array_values($this->feature_opt_ins ?? []);
    }

    /**
     * Whether a feature key is known — i.e. it appears in the feature
     * catalog (`config/features.php`). This is the ONLY server-side gate
     * on opting in: the backend accepts any opt-in for a valid key from
     * any authenticated user. The `beta` flag does NOT gate this. Beta
     * features are hidden for advertisement, not for security — the
     * client decides what to show; the server only rejects junk keys.
     *
     * @param string $key
     * @return bool
     */
    public static function featureExists(string $key): bool
    {
        return in_array($key, Config::get('features.beta', []), true);
    }

    /**
     * Whether a feature is effectively enabled for this user: purely
     * whether they hold an active opt-in record. The opt-in record IS
     * the grant. The `beta` flag only decides what the client advertises
     * in the Labs UI, never what is on. Gated code paths (admin
     * publications, ROR, ...) call this.
     *
     * @param string $key
     * @return bool
     */
    public function hasFeatureEnabled(string $key): bool
    {
        return in_array($key, $this->getActiveFeatureOptIns(), true);
    }

    /**
     * Submissions that belong to the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function submissions(): BelongsToMany
    {
        return $this->belongsToMany(Submission::class)
            ->withTimestamps()
            ->withPivot(['id', 'user_id', 'role', 'submission_id']);
    }

    /**
     * Publications that belong to the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(Publication::class)
            ->withTimestamps()
            ->withPivot('role');
    }

    /**
     * Submission assignments for this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function submissionAssignments(): HasMany
    {
        return $this->hasMany(SubmissionAssignment::class, 'user_id');
    }

    /**
     * Publication assignments for this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function publicationAssignments(): HasMany
    {
        return $this->hasMany(PublicationAssignment::class, 'user_id');
    }

    /**
     * External Identity Providers that belong to the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function externalIdentityProviders(): HasMany
    {
        return $this->hasMany(ExternalIdentityProvider::class);
    }

    /**
     * Whether the user holds the global application-administrator role.
     *
     * @return bool
     */
    public function isApplicationAdministrator(): bool
    {
        return $this->isA(GlobalRole::ApplicationAdministrator->toSlug());
    }

    /**
     * Assign a global role to the user (Bouncer assignment by slug).
     *
     * @param \App\Auth\Roles\GlobalRole $role
     */
    public function assignRole(GlobalRole $role): self
    {
        $this->assign($role->toSlug());

        return $this;
    }

    /**
     * Return the highest privileged role rank for the user (lower ranks higher:
     * application_admin=1 … submitter=6). A UI hint, not authorization.
     *
     * @return int|null
     */
    public function getHighestPrivilegedRole(): ?int
    {
        if ($this->isApplicationAdministrator()) {
            return GlobalRole::ApplicationAdministrator->rank();
        }

        $ranks = [];
        $slugs = PublicationUser::where('user_id', $this->id)->pluck('role')
            ->merge(SubmissionAssignment::where('user_id', $this->id)->pluck('role'));
        foreach ($slugs as $slug) {
            $role = $slug === null ? null : ScopedRole::tryFrom((string)$slug);
            if ($role !== null) {
                $ranks[] = $role->rank();
            }
        }

        return $ranks === [] ? null : min($ranks);
    }

    /**
     * This user's GLOBAL (application-wide) abilities as a map of snake_case
     * ability name => bool, e.g. ['publication_create' => true, ...].
     *
     * Resolved through Bouncer ($this->can) — the same engine the policies use —
     * so these client-facing flags can never drift from real authorization. The
     * keys are derived from {@see GlobalAbility} cases, so adding an ability case
     * (plus its schema field) is all it takes to expose it.
     *
     * Fetched via `currentUser` this is the viewer's own capabilities. These are
     * UI hints only: the server still enforces every mutation with @can.
     *
     * @return array<string, bool>
     */
    public function globalAbilities(): array
    {
        $abilities = [];
        foreach (GlobalAbility::cases() as $ability) {
            $abilities[Str::snake($ability->name)] = $this->can($ability);
        }

        return $abilities;
    }

    /**
     * Create a staged user based on a supplied email and
     * assign a default username and password
     *
     * @param string $email
     * @return self
     */
    public static function createStagedUser(string $email)
    {
        return User::create([
            'username' => User::generateUniqueUsername($email),
            'email' => $email,
            'password' => Hash::make(bin2hex(random_bytes(30))),
            'staged' => 1,
        ]);
    }

    /**
     * Generate a username from an email address and ensure it
     * does not conflict with existing usernames in the application
     * by appending random text to the username when necessary
     *
     * @param string $email
     * @return String
     */
    public static function generateUniqueUsername(string $email)
    {
        if ($email === '') {
            $username = 'user';
        } else {
            $username = explode('@', $email)[0];
        }
        if (User::where('username', $username)->exists()) {
            $unique = $username . '_'
                . self::generateString(random_int(1, 2))
                . str_replace(['0', '1'], '2', (string)random_int(0, 50))
                . self::generateString(random_int(1, 2));
            $username = self::generateUniqueUsername($unique);
        }

        return $username;
    }

    /**
     * Generate a string of random and non-similar-looking characters
     *
     * @param int $length
     * @return string
     */
    private static function generateString($length = 1): string
    {
        $string = '';
        $no_similar_chars = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        for ($i = 0; $i < $length; $i++) {
            $random_int = rand(0, strlen($no_similar_chars) - 1);
            $string .= $no_similar_chars[$random_int];
        }

        return $string;
    }

    /**
     * The identifiable label of the user meant for public display,
     * which prioritizes displaying the user's name over the username
     *
     * @return string
     */
    public function getDisplayLabelAttribute(): string
    {
        return $this->attributes['name'] ?: $this->attributes['username'];
    }

    /**
     * Deterministic avatar color name derived from the user's email (or id
     * when no email is set). Mirrors the legacy client-side hash so existing
     * avatars remain stable while emails are no longer exposed publicly.
     *
     * @return string
     */
    public function getAvatarColorAttribute(): string
    {
        $colors = [
            'blue',
            'cyan',
            'green',
            'magenta',
            'orange',
            'pine',
            'purple',
            'red',
            'yellow',
        ];

        $seed = $this->attributes['email'] ?? (string)$this->attributes['id'];

        $hash = 0;
        $len = strlen($seed);
        for ($i = 0; $i < $len; $i++) {
            $hash = (($hash << 5) - $hash + ord($seed[$i])) & 0xFFFFFFFF;
            if ($hash & 0x80000000) {
                $hash -= 0x100000000;
            }
        }

        return $colors[abs($hash) % count($colors)];
    }

    /**
     * Register the user's media collections. The avatar collection's mechanics
     * live in {@see HasAvatarImage}; moderation of that avatar is a separate
     * concern kept on this model.
     */
    public function registerMediaCollections(): void
    {
        $this->registerAvatarMediaCollection();
    }

    /**
     * Register the user's media conversions (avatar thumb/medium).
     *
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media|null $_media
     *        Required by the Spatie HasMedia contract; unused here.
     */
    public function registerMediaConversions(?Media $_media = null): void
    {
        $this->registerAvatarMediaConversions();
    }

    /**
     * Resolve the GraphQL `User.avatar_upload_blocked` field. True when a
     * moderator has set the avatar-upload-blocked moderation flag on this
     * user. Uploading is allowed by default; the flag is the exception.
     */
    public function getAvatarUploadBlocked(): bool
    {
        return $this->hasModerationFlag(ModerationFlag::AvatarUploadBlocked);
    }

    /**
     * Resolve the GraphQL `can_upload_avatar` field: whether the current viewer
     * may upload an avatar for this user, per the server-owned uploadAvatar
     * gate (owner AND not moderator-blocked). False for guests.
     */
    public function getCanUploadAvatar(): bool
    {
        $viewer = Auth::user();

        return $viewer !== null
            && Gate::forUser($viewer)->allows('uploadAvatar', $this);
    }

    /**
     * Tag every User audit "moderation". Since automatic attribute auditing is
     * off ({@see self::$auditEvents}), the only audits on a User are the
     * moderation custom events, so this tags the whole moderation trail.
     *
     * @return array<int, string>
     */
    public function generateTags(): array
    {
        return ['moderation'];
    }

    /**
     * Record a moderation decision about this user in the durable audit log as
     * a named custom event. The acting moderator is captured automatically as
     * the audit's user; $payload (reporter, reason, notes, reported media uuid)
     * preserves the context of the now-deleted report.
     *
     * @param array<string, mixed> $payload
     */
    public function recordModerationAudit(string $event, array $payload = []): void
    {
        $this->auditEvent = $event;
        $this->isCustomEvent = true;
        $this->auditCustomOld = [];
        $this->auditCustomNew = $payload;
        event(new AuditCustom($this));
    }

    /**
     * This user's moderation history: their moderation audit entries, newest
     * first. Resolves the GraphQL `moderationHistory` field (moderator-gated).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit>
     */
    public function getModerationHistory(): Collection
    {
        return $this->audits()
            ->where('tags', 'like', '%moderation%')
            ->latest()
            ->get();
    }
}
