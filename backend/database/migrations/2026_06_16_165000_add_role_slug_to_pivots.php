<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add a human-readable `role` slug column to the role-assignment pivots and
 * backfill it from the legacy integer `role_id`. Authorization now keys on the
 * slug (App\Auth\ScopedRole is slug-backed); the slug is the vocabulary shared
 * across storage, the ability matrix, and the API.
 *
 * The legacy `role_id` column is deliberately RETAINED (not dropped) so the
 * original data survives this cutover and can repair the slug column if anything
 * goes wrong. It is made nullable here and kept **dual-written** by the role
 * relations / invite mutations (role slug + role_id together), so a rollback to
 * the pre-slug code finds valid role_id data on rows created after this deploy.
 * Dropping `role_id` is a separate, later PR once the slug column is proven.
 */
return new class extends Migration
{
    /** @var array<int, string> */
    private array $tables = [
        'publication_user',
        'submission_user',
        'submission_invitations',
    ];

    /** @var array<int, string> legacy role_id => slug */
    private array $map = [
        1 => 'application_admin',
        2 => 'publication_admin',
        3 => 'editor',
        4 => 'review_coordinator',
        5 => 'reviewer',
        6 => 'submitter',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'role')) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) {
                $t->string('role')->nullable()->after('role_id');
                $t->index('role');
                // Retain role_id as the historical record, but make it nullable:
                // new writes set `role` only and leave role_id null.
                $t->unsignedBigInteger('role_id')->nullable()->change();
            });

            foreach ($this->map as $id => $slug) {
                DB::table($table)->where('role_id', $id)->update(['role' => $slug]);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasColumn($table, 'role')) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) {
                $t->dropIndex(['role']);
                $t->dropColumn('role');
            });
        }
    }
};
