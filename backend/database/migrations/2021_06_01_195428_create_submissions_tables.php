<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmissionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('title', 512)->nullable(false);
            $table->unsignedBigInteger('publication_id');

            $table->foreign('publication_id')
                ->references('id')
                ->on('publications');
        });

        Schema::create('submission_user', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('submission_id');
            $table->unsignedBigInteger('role_id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->foreign('submission_id')
                ->references('id')
                ->on('submissions');

            $table->foreign('role_id')
                ->references('id')
                ->on('roles');

            $table->unique(['user_id', 'submission_id'],'submission_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('submission_user', function (Blueprint $table) {
            $table->dropForeign('submission_user_role_id_foreign');
            $table->dropForeign('submission_user_user_id_foreign');
            $table->dropForeign('submission_user_submission_id_foreign');
        });
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign('submissions_publication_id_foreign');
        });
        Schema::dropIfExists('submission_user');
        Schema::dropIfExists('submissions');
    }
}
