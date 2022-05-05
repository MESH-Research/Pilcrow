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
        Schema::create('overall_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('submission_id');
            $table->text('content');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->unsignedBigInteger('reply_to')->nullable();
            $table->unsignedBigInteger('parent')->nullable();
            $table->timestamps();

            $table->foreign('created_by')
                ->references('id')
                ->on('users');

            $table->foreign('updated_by')
                ->references('id')
                ->on('users');

            $table->foreign('reply_to')
                ->references('id')
                ->on('overall_comments');

            $table->foreign('parent')
                ->references('id')
                ->on('overall_comments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('overall_comments');
    }
};
