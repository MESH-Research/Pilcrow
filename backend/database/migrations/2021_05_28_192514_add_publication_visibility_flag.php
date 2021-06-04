<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPublicationVisibilityFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    private $__visibility_flag = "is_publicly_visible";
    private $__db_table = "publications";

    public function up()
    {
        // This flag is used to indicate the visibility status of a publication
        Schema::table($this->__db_table, function (Blueprint $table) {
            $table->boolean($this->__visibility_flag)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the db column
        if(Schema::hasColumn($this->__db_table, $this->__visibility_flag)) {
            Schema::table($this->__db_table, function (Blueprint $table) {
                $table->dropColumn($this->__visibility_flag);
            });
        }
    }
}
