<?php

use App\Enums\SubmissionFileImportStatus;
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
        Schema::table('submission_files', function (Blueprint $table) {
            $table->unsignedTinyInteger('import_status')->default(SubmissionFileImportStatus::Pending());
            $table->text('error_message')->nullable();
        });

        Schema::table('submission_contents', function (Blueprint $table) {
            $table->longText('data')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('submission_files', function (Blueprint $table) {
            $table->dropColumn('import_status');
            $table->dropColumn('error_message');
        });

        Schema::table('submission_contents', function (Blueprint $table) {
            $table->text('data')->change();
        });
    }
};
