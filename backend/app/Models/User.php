<?php

namespace App\Models;

use App\Rules\Username;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

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
    ];

    /**
     * Rules for creation of a user
     *
     * @return array
     */
    public static function createRules() {
        return [
        'first_name' => 'required|max:55',
        'last_name' => 'required|max:55',
        'username' => ['required', new Username, 'max:15', 'unique:users'],
        'email' => 'email|required|unique:users',
        'password' => 'required|zxcvbn_min:4|max:255',
        ];
    }

    /**
     * Rules for update of a user
     *
     * @return array
     */
    public static function updateRules() {
        return [
            'first_name' => 'sometimes|required|max:55',
            'last_name' => 'sometimes|required|max:55',
            'username' => ['sometimes', 'required', new Username, 'max:15', 'unique:users'],
            'email' => 'sometimes|email|required|unique:users',
            'password' => 'somtimes|required|zxcvbn_min:4|max:255',
            'current_password' => 'required_with:password'
        ];
    }

    /**
     * Lowercase email before saving to persistance.
     *
     * @param string $value
     * @return void
     */
    public function setEmailAttribute($value) {
        $this->attributes['email'] = strtolower($value);
    }
}
