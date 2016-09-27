<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'message';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['message_id', 'queue_id', 'message_content', 'completed'];

    /**
     * Get the associated queues to Message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    /**
     * A Message has many Subscriber.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subscriber()
    {
        return $this->belongsToMany(Subscriber::class, 'sent_message')->withPivot('status')->withTimestamps();
    }
}