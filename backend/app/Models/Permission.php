<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as ParentModel;

class Permission extends ParentModel
{
    use HasFactory;

    public const UPDATE_USERS = 'update users';
    public const UPDATE_USERS_IN_OWN_PUBLICATION = 'update users in own publication';
    public const CREATE_PUBLICATION = 'create publication';
}
