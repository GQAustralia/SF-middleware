<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTableInboundMessageLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inbound_message_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('inbound_sent_message_id');
            $table->integer('response_code');
            $table->text('response_body');
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
        Schema::drop('inbound_message_log');
    }
}