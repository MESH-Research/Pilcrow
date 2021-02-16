<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as ParentModel;

class Permission extends ParentModel
{
    use HasFactory;

    const UPDATE_USERS = 'update users';
    const UPDATE_USERS_IN_OWN_PUBLICATION = 'update users in own publication';
}
