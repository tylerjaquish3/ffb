<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchupComment extends Model
{
    protected $guarded = [];

    public function matchup()
    {
        return $this->belongsTo(Matchup::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
