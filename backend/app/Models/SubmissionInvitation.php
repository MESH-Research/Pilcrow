<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SubmissionInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'expiration',
        'accepted_at',
        'submission_id',
        'token',
    ];

    /**
     * Set a default token and expiration upon creation
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function (SubmissionInvitation $invite) {
            $invite->token = Str::uuid()->toString();
            $invite->expiration = Carbon::now()->addDays(5)->toDateTimeString();
            $invite->save();
        });
    }
}
