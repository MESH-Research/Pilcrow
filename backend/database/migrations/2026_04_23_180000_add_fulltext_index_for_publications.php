<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add a MySQL FULLTEXT index to speed up publications search.
     *
     * LIKE doesn't use it yet — the PublicationBuilder::search scope
     * still runs `LIKE '%term%'` — but having the index in place
     * matches the submissions/users treatment from the previous
     * `add_fulltext_indexes_for_search` migration and leaves the
     * door open to a MATCH...AGAINST switch when search volume
     * justifies it.
     */
    public function up(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->fullText('name', 'publications_name_fulltext');
        });
    }

    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropFullText('publications_name_fulltext');
        });
    }
};
