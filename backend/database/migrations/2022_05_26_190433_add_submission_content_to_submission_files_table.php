<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var string $table_name */
    private $table_name = 'submission_files';

    /** @var string $column_name */
    private $column_name = 'content_id';

    /** @var string $foreign_table_name */
    private $foreign_table_name = 'submission_contents';

    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table_name, function (Blueprint $table) {
            $table->unsignedBigInteger($this->column_name)->nullable();
            $table->foreign($this->column_name)
                ->references('id')
                ->on($this->foreign_table_name);
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn($this->table_name, $this->column_name)) {
            Schema::table($this->table_name, function (Blueprint $table) {
                $table->dropForeign([$this->column_name]);
                $table->dropColumn($this->column_name);
            });
        }
    }
};
