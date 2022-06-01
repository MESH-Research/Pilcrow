<?php

use App\Traits\WithConnectionName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use WithConnectionName;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submissions', function (Blueprint $table) {
            $createdBy = $table->unsignedBigInteger('created_by');
            $updatedBy = $table->unsignedBigInteger('updated_by');

            //SQLite doesn't like non-nullable columns having a NULL default when altering tables
            //This should only affect Cypress CI tests.

            if ($this->connectionIs('sqlite')) {
                $createdBy->nullable();
                $updatedBy->nullable();
            }

            $table->foreign('created_by')
                ->references('id')
                ->on('users');

            $table->foreign('updated_by')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign('submissions_created_by_foreign');
            $table->dropForeign('submissions_updated_by_foreign');
            $table->dropColumn(['created_by', 'updated_by']);
        });
    }
};
