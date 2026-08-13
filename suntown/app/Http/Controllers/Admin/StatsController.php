<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerWeekStat;
use App\Models\StatCategory;
use App\Models\Team;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $season = $request->integer('season', now()->year);
        $week = $request->integer('week', 1);
        $teamId = $request->integer('team_id') ?: null;

        $players = Player::query()
            ->whereHas('rosterPlayer')
            ->with([
                'nflTeam',
                'rosterPlayer.team.user',
                'weekStats' => fn ($q) => $q->where('season', $season)->where('week', $week),
            ])
            ->when($teamId, fn ($q) => $q->whereHas('rosterPlayer', fn ($q2) => $q2->where('team_id', $teamId)))
            ->when($request->filled('position'), fn ($q) => $q->where('position', $request->string('position')))
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('position')
            ->orderBy('name')
            ->get()
            ->map(function (Player $player) {
                $player->existingStats = $player->weekStats->pluck('value', 'stat_category_id');

                return $player;
            });

        $statCategories = StatCategory::orderBy('sort_order')->get();
        $teams = Team::with('user')->orderBy('id')->get();

        return view('admin.stats.index', compact('season', 'week', 'teamId', 'players', 'statCategories', 'teams'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'season' => ['required', 'integer'],
            'week' => ['required', 'integer', 'min:1'],
            'stats' => ['array'],
        ]);

        $season = $validated['season'];
        $week = $validated['week'];

        foreach ($validated['stats'] ?? [] as $playerId => $categoryValues) {
            foreach ($categoryValues as $categoryId => $value) {
                if ($value === '' || $value === null) {
                    PlayerWeekStat::where('player_id', $playerId)
                        ->where('season', $season)
                        ->where('week', $week)
                        ->where('stat_category_id', $categoryId)
                        ->delete();

                    continue;
                }

                PlayerWeekStat::updateOrCreate(
                    [
                        'player_id' => $playerId,
                        'season' => $season,
                        'week' => $week,
                        'stat_category_id' => $categoryId,
                    ],
                    ['value' => $value]
                );
            }
        }

        return redirect()->route('admin.stats.index', $request->only(['season', 'week', 'team_id']))
            ->with('status', 'Stats saved.');
    }
}
