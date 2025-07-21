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

        Schema::create('meta_prompt_sets', function (Blueprint $table) {
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
            $table->foreignId('meta_prompt_set_id')
                ->constrained('meta_prompt_sets');
            $table->enum('type', array_column(MetaPromptType::cases(), 'value'));
            $table->json('options')->nullable();
            $table->boolean('required')->default(false);
            $table->integer('order')->default(0);
            $table->softDeletes();
        });

        Schema::create('meta_answers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('prompt_id')->constrained('meta_prompts');
            $table->text('prompt');
            $table->json('answer');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->string('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta_answers');
        Schema::dropIfExists('meta_prompts');
        Schema::dropIfExists('meta_prompt_sets');
    }
};
