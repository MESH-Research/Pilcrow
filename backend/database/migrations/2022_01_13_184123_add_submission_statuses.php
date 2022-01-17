<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubmissionStatuses extends Migration
{
    /** @var string $table_name */
    private $table_name = 'submissions';

    /** @var string $column_name */
    private $column_name = 'status';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table_name, function (Blueprint $table) {
            $table->tinyInteger($this->column_name)->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn($this->table_name, $this->column_name)) {
            Schema::table($this->table_name, function (Blueprint $table) {
                $table->dropColumn($this->column_name);
            });
        }
    }
}
