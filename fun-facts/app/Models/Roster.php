<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roster extends Model
{
    protected $guarded = [];

    protected $table = 'rosters';

    public $timestamps = false;

    public function stat()
    {
        return $this->hasOne('App\Models\Stat');
    }
}
