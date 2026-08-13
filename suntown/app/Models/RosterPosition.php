<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RosterPosition extends Model
{
    protected $guarded = [];

    const BENCH_CODE = 'BN';

    const IR_CODE = 'IR';

    protected $casts = [
        'eligible_positions' => 'array',
    ];

    public function draftPicks()
    {
        return $this->hasMany(DraftPick::class);
    }

    public function lineups()
    {
        return $this->hasMany(Lineup::class);
    }

    public function isBench(): bool
    {
        return $this->code === self::BENCH_CODE;
    }

    public function isIR(): bool
    {
        return $this->code === self::IR_CODE;
    }

    /**
     * Total roster size: the sum of every configured slot, including bench
     * but excluding IR — IR slots are exempt from the roster limit, not an
     * addition to it (see Team::rosterCountForLimit()).
     */
    public static function rosterLimit(): int
    {
        return (int) static::where('code', '!=', self::IR_CODE)->sum('slot_count');
    }
}
