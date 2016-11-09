<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesForceLog extends Model
{
    protected $table = 'salesforce_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['object_name', 'message', 'response_body'];
}
