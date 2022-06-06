<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->string('home_page_content')->nullable();
            $table->string('new_submission_content')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('home_page_content', 'new_submission_content')) {
            Schema::table('publications', function (Blueprint $table) {
                $table->dropColumn('home_page_content');
                $table->dropColumn('new_submission_content');
            });
        }

    }
};
