<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonPosition extends Model
{
    /**
     * The attributes that are NOT mass assignable.
     */
    protected $guarded = [];

    protected $table = 'season_positions';

    public $timestamps = false;

}
