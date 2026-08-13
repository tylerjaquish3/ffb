<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\RosterPosition;
use App\Models\WaiverClaim;
use App\Support\Season;
use Illuminate\Http\Request;

class WaiverController extends Controller
{
    public function create(Request $request, Player $player)
    {
        $team = $request->user()->team;
        abort_unless($team, 403);
        abort_unless($player->isOnWaivers(), 400);

        $season = now()->year;
        $week = Season::currentWeek($season);
        $limit = RosterPosition::rosterLimit();
        $currentCount = $team->rosterCountForLimit($season);
        $needsDrop = $currentCount >= $limit;

        $existingClaim = WaiverClaim::where('team_id', $team->id)
            ->where('player_id', $player->id)
            ->where('season', $season)
            ->where('status', WaiverClaim::STATUS_PENDING)
            ->first();

        $rosterPlayers = $needsDrop
            ? $team->players()->with('nflTeam')->orderBy('position')->orderBy('name')->get()
                ->reject(fn (Player $p) => $p->isLockedForWeek($season, $week))
            : collect();

        return view('waivers.create', [
            'player' => $player,
            'team' => $team,
            'season' => $season,
            'limit' => $limit,
            'currentCount' => $currentCount,
            'needsDrop' => $needsDrop,
            'rosterPlayers' => $rosterPlayers,
            'remainingBudget' => $team->fabBudgetRemaining($season) + ($existingClaim->amount ?? 0),
            'existingClaim' => $existingClaim,
            'clearsAt' => $player->waiverClearsAt(),
        ]);
    }

    public function store(Request $request, Player $player)
    {
        $team = $request->user()->team;
        abort_unless($team, 403);
        abort_unless($player->isOnWaivers(), 400);

        $season = now()->year;

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:0'],
            'drop_player_id' => ['nullable', 'integer', 'exists:players,id'],
        ]);

        $existingClaim = WaiverClaim::where('team_id', $team->id)
            ->where('player_id', $player->id)
            ->where('season', $season)
            ->where('status', WaiverClaim::STATUS_PENDING)
            ->first();

        // Budget check excludes this claim's own prior amount so editing a
        // bid down (or leaving it the same) never falsely reads as over.
        $remaining = $team->fabBudgetRemaining($season) + ($existingClaim->amount ?? 0);

        if ($validated['amount'] > $remaining) {
            return back()->withErrors(['amount' => "You only have \${$remaining} in uncommitted FAB budget."])->withInput();
        }

        $limit = RosterPosition::rosterLimit();
        $currentCount = $team->rosterCountForLimit($season);
        $needsDrop = $currentCount >= $limit;

        $dropPlayer = null;
        if ($needsDrop) {
            $dropPlayer = $validated['drop_player_id']
                ? $team->players()->find($validated['drop_player_id'])
                : null;

            if ($dropPlayer?->isLockedForWeek($season, Season::currentWeek($season))) {
                return back()->withErrors(['drop_player_id' => "{$dropPlayer->name}'s game has already started — they're locked until waivers clear Tuesday night."])->withInput();
            }

            if (! $dropPlayer) {
                return back()->withErrors(['drop_player_id' => "Your roster is full ({$currentCount}/{$limit}) — pick who you'd drop if this bid wins."])->withInput();
            }
        }

        WaiverClaim::updateOrCreate(
            [
                'team_id' => $team->id,
                'player_id' => $player->id,
                'season' => $season,
                'status' => WaiverClaim::STATUS_PENDING,
            ],
            [
                'amount' => $validated['amount'],
                'drop_player_id' => $dropPlayer?->id,
            ]
        );

        return redirect()->route('players.index')->with('status', "Bid of \${$validated['amount']} placed on {$player->name}.");
    }
}
