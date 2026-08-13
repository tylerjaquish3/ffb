<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradeVeto extends Model
{
    protected $table = 'trade_vetoes';

    protected $guarded = [];

    public function trade()
    {
        return $this->belongsTo(Trade::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
