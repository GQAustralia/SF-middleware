<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OutboundMessageLog extends Model
{
    protected $table = 'outbound_message_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['outbound_message_id', 'operation', 'request_object', 'object_name'];

    /**
     * An outbound log belongs to an outbound message
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function message()
    {
        return $this->belongsTo(OutboundMessage::class);
    }
}
