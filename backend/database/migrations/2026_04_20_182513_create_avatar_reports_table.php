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
        // An avatar report is a transient queue item: it exists only while
        // pending review. On resolution the moderator's decision is written to
        // the durable audit log (see User::recordModerationAudit) and the
        // report row — plus its private snapshot — is deleted. So there is no
        // status / resolved-by / resolved-at / resolution-notes here; the queue
        // never holds anything but pending reports.
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
            $table->timestamps();

            $table->index('user_id');
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
