<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerWeekStat extends Model
{
    protected $guarded = [];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function statCategory()
    {
        return $this->belongsTo(StatCategory::class);
    }

    public function getPointsAttribute(): float
    {
        return $this->statCategory->pointsFor($this->value);
    }
}
