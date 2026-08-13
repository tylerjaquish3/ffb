<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lineup extends Model
{
    protected $guarded = [];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function rosterPosition()
    {
        return $this->belongsTo(RosterPosition::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
