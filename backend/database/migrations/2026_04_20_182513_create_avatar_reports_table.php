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
        Schema::create('avatar_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->comment('User whose avatar was reported')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('reporter_user_id')
                ->nullable()
                ->comment('User who filed the report; null if they have since been deleted')
                ->constrained('users')
                ->nullOnDelete();
            $table->text('reason')->nullable();
            $table->string('status', 16)->default('pending')
                ->comment('pending | dismissed | removed');
            $table->foreignId('resolved_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avatar_reports');
    }
};
