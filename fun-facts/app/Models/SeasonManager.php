<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonManager extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];

    protected $table = 'season_managers';

    public $timestamps = false;

    
}
