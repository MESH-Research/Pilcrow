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
 * Bouncer + the code ability matrix (App\Auth\RoleAbilities) rather than the FK.
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
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreign('role_id')->references('id')->on('roles');
            });
        }
    }
};
