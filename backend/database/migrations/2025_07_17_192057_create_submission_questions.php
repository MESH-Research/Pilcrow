<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('submission_meta_question_sets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->foreignId('publication_id')
                ->constrained('publications');
            $table->softDeletes();
        });

        Schema::create('submission_meta_questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->fullText('caption')->nullable();
            $table->foreignId('submission_meta_question_set_id')
                ->constrained('submission_meta_question_sets');
            $table->text('question');
            $table->string('type');
            $table->boolean('required')->default(false);
            $table->integer('order')->default(0);
            $table->softDeletes();
        });

        Schema::table('submission_meta_answers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('submission_meta_question_id')->constrained('submission_meta_questions');
            $table->text('question');
            $table->json('answer');
            $table->string('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_questions');
    }
};
