<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [];

    const TYPE_ADD = 'add';

    const TYPE_DROP = 'drop';

    const TYPE_TRADE = 'trade';

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function counterpartyTeam()
    {
        return $this->belongsTo(Team::class, 'counterparty_team_id');
    }

    public function trade()
    {
        return $this->belongsTo(Trade::class);
    }
}
