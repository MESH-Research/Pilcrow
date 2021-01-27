<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as ParentModel;

class Role extends ParentModel
{
    use HasFactory;

    const APPLICATION_ADMINISTRATOR = 'Application Administrator';
    const PUBLICATION_ADMINISTRATOR = 'Publication Administrator';
    const EDITOR = 'Editor';
    const REVIEW_COORDINATOR = 'Review Coordinator';
    const REVIEWER = 'Reviewer';
    const SUBMITTER = 'Submitter';

}
