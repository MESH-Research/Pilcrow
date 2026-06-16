<?php
declare(strict_types=1);

namespace App\Models;

use App\Builders\UserBuilder;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Attributes\SearchUsingPrefix;
use Laravel\Scout\Searchable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use Notifiable;
    use HasApiTokens;
    use HasRoles;
    use Searchable;

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
     * Return the highest privileged role slug for the user in the following
     * order: application_admin, publication_admin, editor, review_coordinator,
     * reviewer, submitter.
     *
     * @return string|null
     */
    public function getHighestPrivilegedRole(): ?string
    {
        if ($this->hasRole(Role::APPLICATION_ADMINISTRATOR)) {
            return Role::SLUG_APPLICATION_ADMIN;
        }
        if ($this->publications->isNotEmpty()) {
            return Role::mostPrivileged(
                PublicationUser::where('user_id', $this->id)->pluck('role')
            );
        }
        if ($this->submissions->isNotEmpty()) {
            return Role::mostPrivileged(
                SubmissionAssignment::where('user_id', $this->id)->pluck('role')
            );
        }

        return null;
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
}
