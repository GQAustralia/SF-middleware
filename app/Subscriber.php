<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $table = 'subscriber';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['platform_name', 'url'];

    /**
     * A Subscriber belongs to many Action.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function action()
    {
        return $this->belongsToMany(Action::class)->withTimestamps();
    }

    /**
     * A Subscriber belongs to many Message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function message()
    {
        return $this->belongsToMany(Message::class, 'sent_message')->withPivot('status')->withTimestamps();
    }
}
