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
        Schema::create('submission_contents', function (Blueprint $table) {
            $table->id();
            $table->text('data');
            $table->unsignedBigInteger('submission_file_id')->nullable();
            $table->foreign('submission_file_id')
                ->references('id')
                ->on('submission_files');
            $table->unsignedBigInteger('submission_id');
            $table->foreign('submission_id')
                ->references('id')
                ->on('submissions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('submission_contents');
    }
};
