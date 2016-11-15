<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTableInboundMessage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inbound_message', function (Blueprint $table) {
            $table->increments('id');
            $table->string('message_id')->unique();
            $table->integer('action_id');
            $table->text('message_content');
            $table->enum('completed', ['Y', 'N'])->default('N');
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
        Schema::drop('inbound_message');
    }
}
