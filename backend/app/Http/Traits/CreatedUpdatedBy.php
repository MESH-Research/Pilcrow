<?php
declare(strict_types=1);

namespace App\Http\Traits;

trait CreatedUpdatedBy
{
    /**
     * Add functionality to automatically update the database upon events
     *
     * @return void
     */
    public static function bootCreatedUpdatedBy()
    {
        // Auto-populate created_by / updated_by from the authenticated
        // user. Skip silently when no user is authenticated (seeders,
        // queue workers, artisan commands) so non-HTTP contexts can
        // still save models that explicitly set these columns.
        static::creating(function ($model) {
            $userId = auth()->user()?->id;
            if ($userId !== null) {
                if (!$model->isDirty('created_by')) {
                    $model->created_by = $userId;
                }
                if (!$model->isDirty('updated_by')) {
                    $model->updated_by = $userId;
                }
            }
        });

        static::updating(function ($model) {
            $userId = auth()->user()?->id;
            if ($userId !== null && !$model->isDirty('updated_by')) {
                $model->updated_by = $userId;
            }
        });
    }
}
