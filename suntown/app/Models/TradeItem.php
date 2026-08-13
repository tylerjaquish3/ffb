<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradeItem extends Model
{
    protected $guarded = [];

    public function trade()
    {
        return $this->belongsTo(Trade::class);
    }

    /**
     * The team giving up this player.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
