<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeagueSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'trade_deadline' => 'date',
    ];

    public function tradeDeadlinePassed(): bool
    {
        return $this->trade_deadline !== null && now()->startOfDay()->gt($this->trade_deadline);
    }

    /**
     * Single hardcoded league, single settings row. Creates it with
     * defaults on first access so nothing else has to worry about it
     * not existing yet.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
