<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enforce "at most one report per (reporter, reported user, reported media)" at
 * the database layer, closing the read-then-write race in ReportUserAvatar (two
 * concurrent identical reports could both pass the existing-report check and
 * both insert).
 *
 * Reports are deleted on resolution, so the only rows that ever exist are
 * pending ones — a plain unique index therefore already behaves as "unique
 * among pending reports": a resolved report leaves no row, so the same reporter
 * can report a future avatar, or re-report after a dismissal. No partial index
 * (which MySQL lacks anyway) is needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avatar_reports', function (Blueprint $table) {
            $table->unique(
                ['reporter_user_id', 'user_id', 'reported_media_uuid'],
                'avatar_reports_pending_dedup_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('avatar_reports', function (Blueprint $table) {
            $table->dropUnique('avatar_reports_pending_dedup_unique');
        });
    }
};
