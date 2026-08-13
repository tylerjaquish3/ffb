<?php

namespace App\Http\Controllers;

use App\Models\Lineup;
use App\Models\Player;
use App\Models\RosterPlayer;
use App\Models\RosterPosition;
use App\Models\Transaction;
use App\Support\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddPlayerController extends Controller
{
    public function create(Request $request, Player $player)
    {
        $team = $request->user()->team;
        abort_unless($team, 403);

        if ($player->rosterPlayer) {
            return redirect()->route('players.index')->withErrors(['player' => "{$player->name} is already on a team."]);
        }

        if ($player->isOnWaivers()) {
            return redirect()->route('waivers.create', $player);
        }

        $season = now()->year;
        $week = Season::currentWeek($season);
        $limit = RosterPosition::rosterLimit();
        $currentCount = $team->rosterCountForLimit($season);
        $needsDrop = $currentCount >= $limit;

        $rosterPlayers = $needsDrop
            ? $team->players()->with('nflTeam')->orderBy('position')->orderBy('name')->get()
                ->reject(fn (Player $p) => $p->isLockedForWeek($season, $week))
            : collect();

        return view('players.add', [
            'player' => $player,
            'team' => $team,
            'season' => $season,
            'limit' => $limit,
            'currentCount' => $currentCount,
            'needsDrop' => $needsDrop,
            'rosterPlayers' => $rosterPlayers,
        ]);
    }

    public function store(Request $request, Player $player)
    {
        $team = $request->user()->team;
        abort_unless($team, 403);

        if ($player->rosterPlayer) {
            return redirect()->route('players.index')->withErrors(['player' => "{$player->name} is already on a team."]);
        }

        abort_if($player->isOnWaivers(), 400);

        $validated = $request->validate([
            'drop_player_id' => ['nullable', 'integer', 'exists:players,id'],
        ]);

        $season = now()->year;
        $week = Season::currentWeek($season);
        $limit = RosterPosition::rosterLimit();
        $currentCount = $team->rosterCountForLimit($season);
        $needsDrop = $currentCount >= $limit;

        $dropPlayer = null;
        if ($needsDrop) {
            $dropPlayer = $validated['drop_player_id']
                ? $team->players()->find($validated['drop_player_id'])
                : null;

            if ($dropPlayer?->isLockedForWeek($season, $week)) {
                return back()->withErrors(['drop_player_id' => "{$dropPlayer->name}'s game has already started — they're locked until waivers clear Tuesday night."]);
            }

            if (! $dropPlayer) {
                return back()->withErrors(['drop_player_id' => "Your roster is full ({$currentCount}/{$limit}) — pick a player to drop."]);
            }
        }

        DB::transaction(function () use ($team, $player, $dropPlayer) {
            $season = now()->year;

            if ($dropPlayer) {
                RosterPlayer::where('team_id', $team->id)->where('player_id', $dropPlayer->id)->delete();
                Lineup::where('team_id', $team->id)->where('player_id', $dropPlayer->id)->delete();

                Transaction::create([
                    'type' => Transaction::TYPE_DROP,
                    'season' => $season,
                    'team_id' => $team->id,
                    'player_id' => $dropPlayer->id,
                ]);
            }

            RosterPlayer::create(['team_id' => $team->id, 'player_id' => $player->id]);

            Transaction::create([
                'type' => Transaction::TYPE_ADD,
                'season' => $season,
                'team_id' => $team->id,
                'player_id' => $player->id,
            ]);
        });

        $status = $dropPlayer
            ? "Added {$player->name}, dropped {$dropPlayer->name}."
            : "Added {$player->name}.";

        return redirect()->route('teams.show', $team)->with('status', $status);
    }
}
