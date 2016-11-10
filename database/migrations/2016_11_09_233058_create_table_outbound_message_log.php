<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableOutboundMessageLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outbound_message_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('outbound_message_id');
            $table->string('operation');
            $table->text('request_object');
            $table->text('object_name');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('outbound_message_log');
    }
}
