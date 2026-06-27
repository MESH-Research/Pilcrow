<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pin each avatar report to the exact media row that was reported.
 *
 * Without media_id a report only names the user, so if the user swaps or
 * deletes their avatar before review the moderator sees — and would remove —
 * a different image than the one that was flagged. The FK is nullable with
 * nullOnDelete: when the reported media row is later deleted (the avatar
 * collection is single-file, so re-uploading hard-deletes the old row) the
 * report record survives with media_id = null, signalling "the reported image
 * is no longer the current avatar".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avatar_reports', function (Blueprint $table) {
            $table->foreignId('media_id')
                ->nullable()
                ->after('user_id')
                ->comment('The specific avatar media row that was reported; null once that media is deleted')
                ->constrained('media')
                ->nullOnDelete();
            // Durable identity of the reported media. media_id is nulled by
            // nullOnDelete the moment the user replaces their avatar (the
            // collection is single-file, so the old row is hard-deleted), which
            // would defeat a media_id-based staleness check. The UUID is copied
            // here at report time and never changes, so resolution can still
            // tell whether the current avatar is the one that was reported.
            $table->uuid('reported_media_uuid')
                ->nullable()
                ->after('media_id')
                ->comment('UUID of the reported media; persists after the media row is deleted');
        });
    }

    public function down(): void
    {
        Schema::table('avatar_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('media_id');
            $table->dropColumn('reported_media_uuid');
        });
    }
};
