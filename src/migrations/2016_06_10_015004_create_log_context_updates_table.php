<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogContextUpdatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_context_updates', function (Blueprint $table) {
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
        Schema::drop('log_context_updates');
    }
}
