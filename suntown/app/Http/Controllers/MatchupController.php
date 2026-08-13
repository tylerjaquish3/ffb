<?php

namespace App\Http\Controllers;

use App\Models\Lineup;
use App\Models\Matchup;
use App\Models\Player;
use App\Models\RosterPlayer;
use App\Models\RosterPosition;
use App\Models\Team;
use App\Support\Season;
use Illuminate\Http\Request;

class MatchupController extends Controller
{
    /**
     * Redirect to the current week's matchup for the logged-in user's team.
     */
    public function mine(Request $request)
    {
        $team = $request->user()->team;

        abort_unless($team, 404);

        $season = $request->integer('season', now()->year);
        $week = Season::currentWeek($season);

        $matchup = $team->matchupForWeek($season, $week);

        if (! $matchup) {
            return redirect()->route('dashboard')->with('status', "No matchup scheduled for Week {$week} yet.");
        }

        return redirect()->route('matchups.show', $matchup);
    }

    public function show(Request $request, Matchup $matchup)
    {
        $matchup->load(['homeTeam.user', 'awayTeam.user', 'comments.user']);

        $season = $matchup->season;
        $week = $matchup->week;

        $homeLineups = $this->lineupsBySlot($matchup->homeTeam, $season, $week);
        $awayLineups = $this->lineupsBySlot($matchup->awayTeam, $season, $week);

        // Align rows by the slot definition itself (roster position + slot index)
        // rather than array position, since each team may have a different
        // number of starters filled in at any given moment. IR is excluded
        // here — like bench, it doesn't score and gets its own row group below.
        $rows = RosterPosition::whereNotIn('code', [RosterPosition::BENCH_CODE, RosterPosition::IR_CODE])
            ->orderBy('sort_order')
            ->get()
            ->flatMap(fn (RosterPosition $rp) => collect(range(1, $rp->slot_count))->map(fn ($i) => [
                'label' => $rp->slot_count > 1 ? "{$rp->code} ({$i})" : $rp->code,
                'bench' => false,
                'home' => $this->rowFor($homeLineups->get("{$rp->id}-{$i}"), $season, $week),
                'away' => $this->rowFor($awayLineups->get("{$rp->id}-{$i}"), $season, $week),
            ]));

        $homeBench = $this->benchRows($matchup->home_team_id, $homeLineups, $season, $week);
        $awayBench = $this->benchRows($matchup->away_team_id, $awayLineups, $season, $week);

        foreach (range(0, max($homeBench->count(), $awayBench->count()) - 1) as $i) {
            $rows->push([
                'label' => 'BN',
                'bench' => true,
                'home' => $homeBench->get($i),
                'away' => $awayBench->get($i),
            ]);
        }

        $homeIR = $this->irRows($homeLineups, $season, $week);
        $awayIR = $this->irRows($awayLineups, $season, $week);

        foreach (range(0, max($homeIR->count(), $awayIR->count()) - 1) as $i) {
            $rows->push([
                'label' => 'IR',
                'bench' => true,
                'home' => $homeIR->get($i),
                'away' => $awayIR->get($i),
            ]);
        }

        $homeScore = $matchup->homeScore();
        $awayScore = $matchup->awayScore();

        // Week nav on this page always follows the viewer's own team, not the
        // two teams in the matchup being looked at, so it's useful even when
        // browsing someone else's matchup.
        $myTeam = $request->user()->team;
        $prevMatchup = $myTeam?->matchupForWeek($season, $week - 1);
        $nextMatchup = $myTeam?->matchupForWeek($season, $week + 1);

        $otherMatchups = Matchup::where('season', $season)
            ->where('week', $week)
            ->where('id', '!=', $matchup->id)
            ->with(['homeTeam.user', 'awayTeam.user'])
            ->get();

        return view('matchups.show', compact(
            'matchup', 'rows', 'homeScore', 'awayScore', 'season', 'week',
            'prevMatchup', 'nextMatchup', 'otherMatchups'
        ));
    }

    /**
     * Lineups carry forward unchanged until a manager explicitly saves a
     * week, so this resolves to whichever week's saved rows are actually in
     * effect for the requested week.
     */
    private function lineupsBySlot(Team $team, int $season, int $week)
    {
        return Lineup::where('team_id', $team->id)
            ->where('season', $season)
            ->where('week', $team->resolvedLineupWeek($season, $week))
            ->with('player.nflTeam')
            ->get()
            ->keyBy(fn (Lineup $lineup) => "{$lineup->roster_position_id}-{$lineup->slot_index}");
    }

    /**
     * IR is a Lineup slot like any other, but doesn't score — pulled out of
     * the starting rows and shown in its own group instead, same idea as bench.
     */
    private function irRows($lineupsBySlot, int $season, int $week)
    {
        $irPositionIds = RosterPosition::where('code', RosterPosition::IR_CODE)->pluck('id');

        return $lineupsBySlot
            ->filter(fn (Lineup $lineup) => $irPositionIds->contains($lineup->roster_position_id))
            ->values()
            ->map(fn (Lineup $lineup) => $this->rowFor($lineup, $season, $week));
    }

    /**
     * Bench is implicit: every rostered player without a starting Lineup row
     * for the week is on the bench.
     */
    private function benchRows(int $teamId, $startingLineups, int $season, int $week)
    {
        $startingPlayerIds = $startingLineups->pluck('player_id');

        return RosterPlayer::where('team_id', $teamId)
            ->whereNotIn('player_id', $startingPlayerIds)
            ->with('player.nflTeam')
            ->get()
            ->map(fn (RosterPlayer $rosterPlayer) => $this->rowForPlayer($rosterPlayer->player, $season, $week))
            ->values();
    }

    private function rowFor(?Lineup $lineup, int $season, int $week): ?array
    {
        if (! $lineup || ! $lineup->player) {
            return null;
        }

        return $this->rowForPlayer($lineup->player, $season, $week);
    }

    private function rowForPlayer(Player $player, int $season, int $week): array
    {
        $game = $player->nflGameForWeek($season, $week);

        return [
            'player' => $player,
            'points' => $player->pointsForWeek($season, $week),
            'game' => $game ? [
                'opponent' => $game->opponentFor($player->nfl_team_id),
                'is_home' => $game->isHomeFor($player->nfl_team_id),
                'kickoff_at' => $game->kickoff_at,
            ] : null,
        ];
    }
}
