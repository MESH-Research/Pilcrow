<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Drop the orphaned spatie/laravel-permission tables. Authorization is now
 * handled by Bouncer (bouncer_* tables) plus the role-slug pivots; the global
 * application-administrator role lives in Bouncer's assigned_roles. Nothing
 * references these tables anymore.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Child/pivot tables first to satisfy foreign keys.
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }

    public function down(): void
    {
        // Irreversible: these legacy tables are superseded by Bouncer. Restore
        // by reinstalling spatie/laravel-permission and its migration if ever
        // needed.
    }
};
