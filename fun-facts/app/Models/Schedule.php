<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    /**
     * The attributes that are NOT mass assignable.
     */
    protected $guarded = [];

    protected $table = 'schedule';

    public $timestamps = false;

}
