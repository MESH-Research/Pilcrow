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
        Schema::create('submission_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->text('message')->nullable();
            $table->string('token', 36)->unique()->nullable();
            $table->foreignId('submission_id')->constrained('submissions');
            $table->foreignId('role_id')->constrained('roles');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamp('accepted_at')->nullable();
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
        Schema::dropIfExists('submission_invitations');
    }
};
