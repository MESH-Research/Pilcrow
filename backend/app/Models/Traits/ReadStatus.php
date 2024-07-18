<?php
declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\CommentStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait ReadStatus
{
    /**
     * Returns the associated CommentStatus record
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function readStatus(): HasOne
    {
        return $this->hasOne(CommentStatus::class, 'comment_id')
            ->where('type', static::class)
            ->where('user_id', auth()->id());
    }

    /**
     * Set a value for the read status of a comment.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function readAt(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->readStatus ? $this->readStatus->created_at : null;
            },
            set: function () {
                $this->markRead();
            }
        );
    }

    /**
     * Create a CommentStatus for this
     *
     * @param \App\Models\Traits\User $user
     * @return void
     */
    public function markRead($user = null)
    {
        if (!$user) {
            $user = auth()->user();
        }
        if (!$user) {
            throw new \Exception('Unable to save read status. No user logged in.');
        }

        CommentStatus::create([
            'comment_id' => $this->attributes['id'],
            'user_id' => $user->id,
            'type' => static::class,
        ]);
    }
}
