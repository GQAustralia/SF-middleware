<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OutboundMessage extends Model
{
    protected $table = 'outbound_message';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['message_id', 'message_body', 'message_attributes', 'status'];

    /**
     * An Outbound message has one message log
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function log()
    {
        return $this->hasOne(OutboundMessageLog::class, 'inbound_sent_message');
    }
}
