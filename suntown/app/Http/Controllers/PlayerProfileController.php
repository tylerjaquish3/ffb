<?php

namespace App\Http\Controllers;

use App\Models\NflGame;
use App\Models\Player;
use App\Support\PlayerStatGroups;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PlayerProfileController extends Controller
{
    /**
     * Renders the player-profile modal body as an HTML fragment, fetched
     * on demand by the global modal shell in layouts/app.blade.php.
     */
    public function show(Request $request, Player $player)
    {
        $season = $request->integer('season', now()->year);

        $player->load(['nflTeam', 'rosterPlayer.team']);

        $ownerTeam = $player->team;
        $waiverLockUntil = $player->isOnWaivers() ? $player->waiverClearsAt() : null;

        return response()->view('players.profile', [
            'player' => $player,
            'season' => $season,
            'weeks' => $this->buildGameLog($player, $season),
            'seasonPoints' => $player->pointsForSeason($season),
            'ownerTeam' => $ownerTeam,
            'waiverLockUntil' => $waiverLockUntil,
            'myTeam' => $request->user()->team,
            'statGroups' => PlayerStatGroups::forPosition($player->position),
        ]);
    }

    /**
     * One row per week of the season's real NFL schedule (past or upcoming),
     * zero-filled for any week with no PlayerWeekStat rows yet -- so the log
     * reads as a season schedule, not just a record of what's been manually
     * entered so far. Weeks where the player's team has no game are included
     * as explicit "Bye" rows rather than skipped, so the log always spans
     * every week of the season schedule.
     */
    private function buildGameLog(Player $player, int $season): Collection
    {
        $statsByWeek = $player->weekStats()
            ->where('season', $season)
            ->with('statCategory')
            ->get()
            ->groupBy('week');

        if (! $player->nfl_team_id) {
            return $statsByWeek->keys()->sortDesc()->map(fn ($week) => [
                'week' => $week,
                'points' => $statsByWeek->get($week)->sum(fn ($stat) => $stat->points),
                'valuesByCode' => $statsByWeek->get($week)->pluck('value', 'statCategory.code'),
                'game' => null,
                'isBye' => false,
            ])->values();
        }

        $seasonWeeks = NflGame::where('season', $season)->distinct()->orderBy('week')->pluck('week');

        $gamesByWeek = NflGame::where('season', $season)
            ->where(function ($q) use ($player) {
                $q->where('home_nfl_team_id', $player->nfl_team_id)
                    ->orWhere('away_nfl_team_id', $player->nfl_team_id);
            })
            ->get()
            ->keyBy('week');

        return $seasonWeeks->map(function ($week) use ($player, $gamesByWeek, $statsByWeek) {
            $game = $gamesByWeek->get($week);
            $stats = $statsByWeek->get($week, collect());

            return [
                'week' => $week,
                'points' => $stats->sum(fn ($stat) => $stat->points),
                'valuesByCode' => $stats->pluck('value', 'statCategory.code'),
                'game' => $game ? [
                    'opponent' => $game->opponentFor($player->nfl_team_id),
                    'is_home' => $game->isHomeFor($player->nfl_team_id),
                ] : null,
                'isBye' => ! $game,
            ];
        })->values();
    }
}
