<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NflGame;
use App\Models\Player;
use App\Support\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class DraftImportController extends Controller
{
    public function index(Request $request)
    {
        $season = $request->integer('season', now()->year);
        $week = $request->integer('week', Season::currentWeek($season));

        $preview = null;
        try {
            $source = DB::connection('draft_source');
            $league = $source->table('leagues')->where('name', 'Suntown FFB')->first();
            if ($league) {
                $managerIds = $source->table('league_managers')->where('league_id', $league->id)->pluck('id');
                $preview = [
                    'pick_count' => $source->table('draft_selections')
                        ->whereIn('manager_id', $managerIds)
                        ->where('year', $season)
                        ->count(),
                    'source_player_count' => $source->table('players')->count(),
                ];
            }
        } catch (\Throwable $e) {
            $preview = ['error' => $e->getMessage()];
        }

        $localPlayerCount = Player::count();
        $localGameCount = NflGame::where('season', $season)->count();
        $injuredPlayerCount = Player::whereNotNull('injury_status')->count();

        return view('admin.draft-import.index', compact('season', 'week', 'preview', 'localPlayerCount', 'localGameCount', 'injuredPlayerCount'));
    }

    public function syncPlayers(Request $request)
    {
        $exitCode = Artisan::call('players:import');
        $output = Artisan::output();

        return back()
            ->with('status', $exitCode === 0 ? 'Player pool synced.' : 'Player sync failed.')
            ->with('importOutput', $output);
    }

    public function syncSchedule(Request $request)
    {
        $validated = $request->validate([
            'season' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        $exitCode = Artisan::call('nfl-schedule:import', ['season' => $validated['season']]);
        $output = Artisan::output();

        return back()
            ->with('status', $exitCode === 0 ? 'NFL schedule synced.' : 'NFL schedule sync failed.')
            ->with('importOutput', $output);
    }

    public function syncInjuries(Request $request)
    {
        $validated = $request->validate([
            'season' => ['required', 'integer', 'min:2000', 'max:2100'],
            'week' => ['required', 'integer', 'min:1', 'max:22'],
        ]);

        $exitCode = Artisan::call('sportradar:injuries', [
            'season' => $validated['season'],
            'week' => $validated['week'],
        ]);
        $output = Artisan::output();

        return back()
            ->with('status', $exitCode === 0 ? 'Injury report synced.' : 'Injury sync failed.')
            ->with('importOutput', $output);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'season' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        $exitCode = Artisan::call('draft:import', ['season' => $validated['season']]);
        $output = Artisan::output();

        return back()
            ->with('status', $exitCode === 0 ? 'Draft import complete.' : 'Draft import failed.')
            ->with('importOutput', $output);
    }
}
