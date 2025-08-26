<?php
declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Auth;

trait HasUserAuditFields
{
    /**
     * Add functionality to automatically update the database upon events
     *
     * @return void
     */
    public static function bootHasUserAuditFields()
    {
        // This automatically updates the created_by and updated_by fields when the model is created
        static::creating(function ($model) {
            if (!$model->isDirty('created_by')) {
                $model->created_by = Auth::user()->id;
            }
            if (!$model->isDirty('updated_by')) {
                $model->updated_by = Auth::user()->id;
            }
        });

        // This automatically updates the updated_by field when the model is updated
        static::updating(function ($model) {
            if (!$model->isDirty('updated_by')) {
                $model->updated_by = Auth::user()->id;
            }
        });
    }
}
