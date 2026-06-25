<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop the foreign keys from the role-assignment pivots to the spatie roles
 * table so that table can be dropped when spatie/laravel-permission is retired.
 *
 * The integer role_id column is retained; authorization now resolves it through
 * Bouncer + the code ability matrix (App\Auth\Roles\ScopedRole) rather than the FK.
 * Replacing role_id with a human-readable slug is deliberately deferred to a
 * follow-on PR focused on that vocabulary change in isolation.
 */
return new class extends Migration
{
    /** @var array<int, string> */
    private array $tables = [
        'publication_user',
        'submission_user',
        'submission_invitations',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['role_id']);
            });
        }
    }

    public function down(): void
    {
        // The spatie `roles` table is dropped by a LATER migration whose own
        // down() is a deliberate no-op (the table is superseded, not restored).
        // On a full `migrate:rollback` this down() runs after that, so the FK
        // target may be gone — re-adding it would abort the whole rollback.
        // Only restore the FK when `roles` still exists (e.g. a single-step
        // rollback of just this migration); otherwise skip so rollback completes.
        if (!Schema::hasTable('roles')) {
            return;
        }

        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreign('role_id')->references('id')->on('roles');
            });
        }
    }
};
