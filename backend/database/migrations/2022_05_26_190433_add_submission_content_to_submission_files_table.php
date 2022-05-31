<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submission_files', function (Blueprint $table) {
            $table->unsignedBigInteger('content_id')->nullable();
            $table->foreign('content_id')
                ->references('id')
                ->on('submission_contents');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('submission_files', 'content_id')) {
            Schema::table('submission_files', function (Blueprint $table) {
                $table->dropForeign(['content_id']);
                $table->dropColumn('content_id');
            });
        }
    }
};
