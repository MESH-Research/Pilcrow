<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'expiration',
        'registered_at',
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
        static::created(function (Invitation $invite) {
            $invite->token = Str::uuid()->toString();
            $invite->expiration = Carbon::now()->addDays(5)->toDateTimeString();
            $invite->save();
        });
    }
}
