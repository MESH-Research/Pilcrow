<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add a `moderation_flags` column backing per-user moderation state
     * (e.g. avatar-upload blocked). A flat JSON array of active flag keys,
     * mirroring `feature_opt_ins`: presence of a key means the flag is set.
     * Nullable so existing users need no backfill — an absent column means
     * no flags, which is the default (e.g. uploads allowed).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('moderation_flags')->nullable()->after('feature_opt_ins');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('moderation_flags');
        });
    }
};
