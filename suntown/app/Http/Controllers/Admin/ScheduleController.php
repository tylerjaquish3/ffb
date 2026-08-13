<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Matchup;
use App\Models\Team;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $season = $request->integer('season', now()->year);

        $matchups = Matchup::where('season', $season)
            ->with(['homeTeam.user', 'awayTeam.user'])
            ->orderBy('week')
            ->get()
            ->groupBy('week');

        $teams = Team::with('user')->orderBy('id')->get();

        return view('admin.schedule.index', compact('season', 'matchups', 'teams'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'season' => ['required', 'integer'],
            'weeks_count' => ['required', 'integer', 'min:1', 'max:25'],
        ]);

        $season = $validated['season'];
        $weeksCount = $validated['weeks_count'];

        $teamIds = Team::orderBy('id')->pluck('id')->all();

        if (count($teamIds) < 2) {
            return back()->withErrors(['weeks_count' => 'Need at least 2 teams to generate a schedule.']);
        }

        $rounds = $this->roundRobinRounds($teamIds);
        $roundsCount = count($rounds);

        Matchup::where('season', $season)->delete();

        for ($week = 1; $week <= $weeksCount; $week++) {
            $cycleIndex = intdiv($week - 1, $roundsCount);
            $round = $rounds[($week - 1) % $roundsCount];

            foreach ($round as $pair) {
                [$teamA, $teamB] = $pair;
                [$home, $away] = $cycleIndex % 2 === 0 ? [$teamA, $teamB] : [$teamB, $teamA];

                Matchup::create([
                    'season' => $season,
                    'week' => $week,
                    'home_team_id' => $home,
                    'away_team_id' => $away,
                ]);
            }
        }

        return redirect()->route('admin.schedule.index', ['season' => $season])
            ->with('status', "Generated a {$weeksCount}-week schedule.");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'season' => ['required', 'integer'],
            'week' => ['required', 'integer', 'min:1'],
            'home_team_id' => ['required', 'integer', 'different:away_team_id', 'exists:teams,id'],
            'away_team_id' => ['required', 'integer', 'exists:teams,id'],
        ]);

        Matchup::create($validated);

        return redirect()->route('admin.schedule.index', ['season' => $validated['season']])
            ->with('status', 'Matchup added.');
    }

    public function destroy(Matchup $matchup)
    {
        $season = $matchup->season;
        $matchup->delete();

        return redirect()->route('admin.schedule.index', ['season' => $season])
            ->with('status', 'Matchup removed.');
    }

    /**
     * Standard "circle method" round robin: returns an array of rounds,
     * each an array of [teamIdA, teamIdB] pairs.
     */
    private function roundRobinRounds(array $teamIds): array
    {
        if (count($teamIds) % 2 !== 0) {
            $teamIds[] = null; // bye
        }

        $n = count($teamIds);
        $rounds = [];

        for ($round = 0; $round < $n - 1; $round++) {
            $pairs = [];
            for ($i = 0; $i < $n / 2; $i++) {
                $home = $teamIds[$i];
                $away = $teamIds[$n - 1 - $i];
                if ($home !== null && $away !== null) {
                    $pairs[] = [$home, $away];
                }
            }
            $rounds[] = $pairs;

            $fixed = $teamIds[0];
            $rest = array_slice($teamIds, 1);
            array_unshift($rest, array_pop($rest));
            $teamIds = array_merge([$fixed], $rest);
        }

        return $rounds;
    }
}
