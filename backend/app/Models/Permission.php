<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as ParentModel;

class Permission extends ParentModel
{
    use HasFactory;

    const UPDATE_USER_FOR_OTHERS = 'update user for others';
}
