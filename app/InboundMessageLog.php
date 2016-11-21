<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InboundMessageLog extends Model
{
    protected $table = 'inbound_message_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['inbound_sent_message_id', 'response_code', 'response_body'];
}
