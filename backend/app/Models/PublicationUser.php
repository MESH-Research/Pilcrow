<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PublicationUser extends Pivot
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
}
