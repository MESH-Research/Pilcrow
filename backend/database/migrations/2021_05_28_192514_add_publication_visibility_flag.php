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
    private $___visibility_flag = "is_publicly_visible";
    private $___db_table = "publications`";

    public function up()
    {
        // This flag is used to indicate the visibility status of a publication
        Schema::table($__db_table, function (Blueprint $table) {
            $table->boolean($__visibility_flag)->default(0);
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
        if(Schema::hasColumn($__db_table, $__visibility_flag)) {
            Schema::table($__db_table, function (Blueprint $table) {
                $table->dropColumn($__visibility_flag);
            });
        }
    }
}
