<?php

use App\Enums\MetaPromptType;
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

        Schema::create('meta_pages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('caption')->nullable();
            $table->foreignId('publication_id')
                ->constrained('publications');
            $table->boolean('required')->default(false);
            $table->integer('order')->default(0);
            $table->softDeletes();
        });

        Schema::create('meta_prompts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('label');
            $table->longText('caption')->nullable();
            $table->foreignId('meta_page_id')
                ->constrained('meta_pages');
            $table->enum('type', array_column(MetaPromptType::cases(), 'value'));
            $table->json('options')->nullable();
            $table->boolean('required')->default(false);
            $table->integer('order')->default(0);
            $table->softDeletes();
        });

        Schema::create('submission_meta_responses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('meta_page_id')->constrained('meta_pages');
            $table->foreignId('submission_id')->constrained('submissions');
            $table->index(['meta_page_id', 'submission_id']);
            $table->json('prompts');
            $table->json('responses');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_meta_responses');
        Schema::dropIfExists('meta_prompts');
        Schema::dropIfExists('meta_pages');
    }
};
