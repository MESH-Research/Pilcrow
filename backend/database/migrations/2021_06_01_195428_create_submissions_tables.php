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
            $table->string('title')->nullable(false);
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
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('submissions_users');
        Schema::dropIfExists('publications_submissions');
    }
}
