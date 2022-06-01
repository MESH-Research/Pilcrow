<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicationUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('publication_user', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('publication_id');

            $table->foreign('role_id')
                ->references('id')
                ->on('roles');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->foreign('publication_id')
                ->references('id')
                ->on('publications');

            $table->unique(['user_id','role_id','publication_id'],'publication_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('publication_user', function (Blueprint $table) {
            $table->dropForeign('publication_user_role_id_foreign');
            $table->dropForeign('publication_user_user_id_foreign');
            $table->dropForeign('publication_user_publication_id_foreign');
        });
        Schema::dropIfExists('publication_user');
    }
}
