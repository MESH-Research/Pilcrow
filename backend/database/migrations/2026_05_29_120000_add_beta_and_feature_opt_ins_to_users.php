<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add the two columns that back beta feature gating:
     *
     * - `beta`: admin-set flag granting a user access to private
     *   beta features. Defaults to false so existing users are
     *   non-beta without a backfill.
     * - `feature_opt_ins`: flat JSON array of the feature keys the
     *   user has opted into. Presence of a key is the opt-in; opting
     *   out removes it. Nullable so existing users need no backfill.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('beta')->default(false)->after('staged');
            $table->json('feature_opt_ins')->nullable()->after('beta');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['beta', 'feature_opt_ins']);
        });
    }
};
