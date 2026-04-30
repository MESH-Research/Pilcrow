<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add three JSON columns for user-scoped settings:
     *
     * - `preferences`: known-shape UI prefs (theme, color-blind
     *   patterns). Read every render, written rarely.
     * - `dismissed_ui`: open-ended map of `{key: dismissed_at}` so
     *   "show again after 30 days" can be added later without a
     *   migration.
     * - `feature_opt_ins`: `{feature_key: bool}` flags for opt-in
     *   feature rollouts. History will be picked up via auditing
     *   later if/when a flip-flop log becomes load-bearing.
     *
     * All three are nullable so existing users don't need backfill.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('preferences')->nullable()->after('profile_metadata');
            $table->json('dismissed_ui')->nullable()->after('preferences');
            $table->json('feature_opt_ins')->nullable()->after('dismissed_ui');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'preferences',
                'dismissed_ui',
                'feature_opt_ins',
            ]);
        });
    }
};
