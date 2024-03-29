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
    public const VIEW_ALL_PUBLICATIONS = 'view all publications';
    public const ASSIGN_REVIEWER = 'assign reviewer';
    public const UNASSIGN_REVIEWER = 'unassign reviewer';
    public const ASSIGN_REVIEW_COORDINATOR = 'assign review coordinator';
    public const UNASSIGN_REVIEW_COORDINATOR = 'unassign review coordinator';
    public const ASSIGN_EDITOR = 'assign editor';
    public const UNASSIGN_EDITOR = 'unassign editor';
    public const UPDATE_SITE_SETTINGS = 'update site settings';
}
