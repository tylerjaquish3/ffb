<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = 'stats';

    public $timestamps = false;

    public function roster()
    {
        return $this->hasOne('App\Models\Roster');
    }
}
