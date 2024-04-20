<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentStatus extends Model
{
    protected $fillable = [
        'comment_id',
        'user_id',
        'type'
    ];
}
