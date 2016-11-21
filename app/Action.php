<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    protected $table = 'action';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * An Action has many Inbound Messages.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function inbound_message()
    {
        return $this->hasMany(InboundMessage::class);
    }

    /**
     * An Action belongs to many subscriber.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subscriber()
    {
        return $this->belongsToMany(Subscriber::class)->withTimestamps();
    }
}