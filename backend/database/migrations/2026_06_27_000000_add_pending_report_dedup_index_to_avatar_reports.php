<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enforce "at most one pending report per (reporter, reported user, reported
 * media)" at the database layer, closing the read-then-write race in
 * ReportUserAvatar (two concurrent identical reports could both pass the
 * existing-pending check and both insert).
 *
 * MySQL has no partial/filtered unique index, so we derive a stored column that
 * holds the reported media's durable UUID only while the report is pending
 * (NULL otherwise) and put the unique index over that. NULLs don't collide in a
 * unique index, so resolved reports are unconstrained — the same reporter can
 * report a future avatar, and the column simply goes NULL once a report leaves
 * the queue.
 *
 * The dedup key is derived from reported_media_uuid rather than media_id on
 * purpose: media_id carries an ON DELETE SET NULL foreign key, and MySQL forbids
 * a stored generated column whose base column has that referential action. The
 * UUID identifies the same media, is FK-free, and is durable past media
 * deletion.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avatar_reports', function (Blueprint $table) {
            $table->string('pending_dedup_uuid', 36)
                ->nullable()
                ->storedAs("CASE WHEN status = 'pending' THEN reported_media_uuid ELSE NULL END")
                ->after('reported_media_uuid')
                ->comment('Reported media UUID while pending, NULL otherwise; backs the pending-report unique index');
            $table->unique(
                ['reporter_user_id', 'user_id', 'pending_dedup_uuid'],
                'avatar_reports_pending_dedup_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('avatar_reports', function (Blueprint $table) {
            $table->dropUnique('avatar_reports_pending_dedup_unique');
            $table->dropColumn('pending_dedup_uuid');
        });
    }
};
