<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InboundMessage extends Model
{
    protected $table = 'inbound_message';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['message_id', 'action_id', 'message_content', 'completed'];

    /**
     * Get the associated actions to Inbound Message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function action()
    {
        return $this->belongsTo(Action::class);
    }

    /**
     * A Message has many Subscriber.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subscriber()
    {
        return $this->belongsToMany(Subscriber::class, 'inbound_sent_message')->withPivot('id', 'status')->withTimestamps();
    }
}
