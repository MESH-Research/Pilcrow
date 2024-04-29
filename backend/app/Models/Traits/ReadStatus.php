<?php

namespace App\Models\Traits;

use App\Models\CommentStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait ReadStatus
{
    public function read_status(): HasOne
    {
        return $this->hasOne(CommentStatus::class, 'comment_id')->where('type', static::class)->where('user_id', auth()->id());
    }


    /**
     * Set a value for the read status of a comment.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function readAt(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return $this->read_status ? $this->read_status->created_at : null;
            },
            set: function () {
                $this->markRead();
            }
        );
    }

    public function markRead($user = null)
    {
        if (!$user) {
            $user = auth()->user();
        }
        if (!$user) {
            throw new \Exception('Unable to save read status. No user logged in.');
        }
        CommentStatus::create([
            "comment_id" => $this->id,
            "user_id" => $user->id,
            "type" => static::class,

        ])->save();
    }
}
