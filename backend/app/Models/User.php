<?php
declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Scout\Searchable;

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
    ];

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
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->toArray();

        // Customize array...

        return $array;
    }
}
