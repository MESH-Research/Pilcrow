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
        Schema::create('inline_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('submission_id');
            $table->text('content');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->unsignedBigInteger('reply_to_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->json('style_criteria')->nullable();
            $table->timestamps();

            $table->foreign('created_by')
                ->references('id')
                ->on('users');

            $table->foreign('updated_by')
                ->references('id')
                ->on('users');

            $table->foreign('reply_to_id')
                ->references('id')
                ->on('inline_comments');

            $table->foreign('parent_id')
                ->references('id')
                ->on('inline_comments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inline_comments');
    }
};
