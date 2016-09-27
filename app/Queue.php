<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $table = 'queue';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['queue_name', 'aws_queue_name', 'arn'];

    /**
     * A Queue has many message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function message()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * A Queue belongs to many subscriber.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subscriber()
    {
        return $this->belongsToMany(Subscriber::class)->withTimestamps();
    }
}