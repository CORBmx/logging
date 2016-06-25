<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUpdateLogContextsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('update_log_contexts', function (Blueprint $table) {
            $table->increments('id');
            $table->text('before');
            $table->text('after');
            $table->integer('activity_log_id')->unsigned();

            $table->foreign('activity_log_id')->references('id')->on('activity_logs')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('update_log_contexts');
    }
}
