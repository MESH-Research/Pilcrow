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

    /** @var string $table_name */
    private $table_name = 'submissions';

    /** @var string $column_name */
    private $column_name = 'content_id';

    /** @var string $foreign_table_name */
    private $foreign_table_name = 'submission_contents';

    public function up()
    {
        //
        Schema::table($this->table_name, function (Blueprint $table) {
            $table->unsignedBigInteger($this->column_name)->nullable();
            $table->foreign($this->column_name)->references('id')->on($this->foreign_table_name)->onDelete('cascade');
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
                $table->dropForeign($this->column_name);
                $table->dropColumn($this->column_name);
            });
        }
    }
};
