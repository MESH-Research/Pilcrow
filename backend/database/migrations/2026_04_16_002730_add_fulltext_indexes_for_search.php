<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add MySQL FULLTEXT indexes to speed up search.
     *
     * These indexes are compatible with MATCH...AGAINST queries and,
     * while LIKE does not use them, adding them is non-breaking and
     * prepares the schema for a future switch to FULLTEXT search.
     */
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->fullText('title', 'submissions_title_fulltext');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->fullText(
                ['name', 'email', 'username'],
                'users_name_email_username_fulltext'
            );
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropFullText('submissions_title_fulltext');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropFullText('users_name_email_username_fulltext');
        });
    }
};
