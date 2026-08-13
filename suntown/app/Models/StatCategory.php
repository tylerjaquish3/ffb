<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatCategory extends Model
{
    protected $guarded = [];

    protected $casts = [
        'eligible_positions' => 'array',
    ];

    public function weekStats()
    {
        return $this->hasMany(PlayerWeekStat::class);
    }

    /**
     * Most categories are pure value×rate (base_points 0). A category like
     * points-allowed carries a flat base that decays as the stat value rises.
     */
    public function pointsFor(float $value): float
    {
        return $this->base_points + $value * $this->points_per_unit;
    }
}
