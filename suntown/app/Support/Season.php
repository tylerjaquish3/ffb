<?php

namespace App\Support;

use App\Models\Matchup;
use App\Models\NflGame;
use Illuminate\Support\Carbon;

class Season
{
    /**
     * The earliest week that hasn't finished playing yet, based on real NFL
     * kickoff times. Falls back to the latest scheduled fantasy week (or 1)
     * if the NFL schedule hasn't been imported.
     */
    public static function currentWeek(int $season): int
    {
        $week = NflGame::where('season', $season)
            ->where('kickoff_at', '>=', now())
            ->orderBy('week')
            ->value('week');

        if ($week) {
            return $week;
        }

        return NflGame::where('season', $season)->max('week')
            ?? Matchup::where('season', $season)->max('week')
            ?? 1;
    }

    /**
     * A week only counts toward the record once every real NFL game in it
     * has kicked off — mirrors currentWeek()'s own definition of "finished
     * playing" so a week is never simultaneously "current" and "complete".
     * With no NFL schedule synced for the week, it's treated as not complete.
     */
    public static function isWeekComplete(int $season, int $week): bool
    {
        $latestKickoff = NflGame::where('season', $season)->where('week', $week)->max('kickoff_at');

        if (! $latestKickoff) {
            return false;
        }

        return Carbon::parse($latestKickoff)->lt(now());
    }
}
