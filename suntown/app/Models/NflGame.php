<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NflGame extends Model
{
    protected $guarded = [];

    protected $casts = [
        'kickoff_at' => 'datetime',
    ];

    public function homeTeam()
    {
        return $this->belongsTo(NflTeam::class, 'home_nfl_team_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo(NflTeam::class, 'away_nfl_team_id');
    }

    public function opponentFor(int $nflTeamId): ?NflTeam
    {
        if ($this->home_nfl_team_id === $nflTeamId) {
            return $this->awayTeam;
        }

        if ($this->away_nfl_team_id === $nflTeamId) {
            return $this->homeTeam;
        }

        return null;
    }

    public function isHomeFor(int $nflTeamId): bool
    {
        return $this->home_nfl_team_id === $nflTeamId;
    }
}
