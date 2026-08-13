<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NflTeam extends Model
{
    protected $guarded = [];

    public function players()
    {
        return $this->hasMany(Player::class);
    }

    public function gameForWeek(int $season, int $week): ?NflGame
    {
        return NflGame::where('season', $season)
            ->where('week', $week)
            ->where(function ($q) {
                $q->where('home_nfl_team_id', $this->id)
                    ->orWhere('away_nfl_team_id', $this->id);
            })
            ->first();
    }

    public function helmetImage(): string
    {
        return asset('images/helmets/'.$this->abbr.'.png');
    }

    public function wordmarkImage(): string
    {
        return asset('images/wordmarks/'.$this->abbr.'.png');
    }
}
