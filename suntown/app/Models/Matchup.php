<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matchup extends Model
{
    protected $guarded = [];

    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function comments()
    {
        return $this->hasMany(MatchupComment::class)->oldest();
    }

    public function homeScore(): float
    {
        return $this->homeTeam->scoreForWeek($this->season, $this->week);
    }

    public function awayScore(): float
    {
        return $this->awayTeam->scoreForWeek($this->season, $this->week);
    }

    public function winnerTeam(): ?Team
    {
        $home = $this->homeScore();
        $away = $this->awayScore();

        if ($home === $away) {
            return null;
        }

        return $home > $away ? $this->homeTeam : $this->awayTeam;
    }
}
