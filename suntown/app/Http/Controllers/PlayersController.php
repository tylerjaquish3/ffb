<?php

namespace App\Http\Controllers;

use App\Models\NflTeam;
use App\Models\Player;
use App\Models\WaiverClaim;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PlayersController extends Controller
{
    private const PER_PAGE = 50;

    public function index(Request $request)
    {
        $season = $request->integer('season', now()->year);

        WaiverClaim::processDueWaivers($season);

        // Defaults to free-agent/waiver players only until the manager explicitly picks another filter.
        $ownership = $request->has('ownership') ? $request->input('ownership') : 'free_agent';

        $myTeam = $request->user()->team;

        $myPendingBids = $myTeam
            ? WaiverClaim::where('team_id', $myTeam->id)->where('season', $season)->where('status', WaiverClaim::STATUS_PENDING)->pluck('amount', 'player_id')
            : collect();

        // Season points come from live-computed stat×points_per_unit sums, not a
        // sortable column, so the full filtered set is sorted in PHP before paging.
        $allPlayers = Player::query()
            ->with([
                'nflTeam',
                'rosterPlayer.team.user',
                'weekStats' => fn ($q) => $q->where('season', $season)->with('statCategory'),
            ])
            ->when($request->filled('position'), fn ($q) => $q->where('position', $request->string('position')))
            ->when($request->filled('nfl_team_id'), fn ($q) => $q->where('nfl_team_id', $request->integer('nfl_team_id')))
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($ownership === 'free_agent', fn ($q) => $q->whereDoesntHave('rosterPlayer'))
            ->when($ownership === 'owned', fn ($q) => $q->whereHas('rosterPlayer'))
            ->get()
            ->map(function (Player $player) use ($myPendingBids) {
                $player->seasonPoints = $player->weekStats->sum(fn ($stat) => $stat->points);
                $player->waiverLockUntil = $player->isOnWaivers() ? $player->waiverClearsAt() : null;
                $player->myBidAmount = $myPendingBids->get($player->id);

                return $player;
            })
            ->sortByDesc('seasonPoints')
            ->values();

        $page = $request->integer('page', 1);

        $players = new LengthAwarePaginator(
            $allPlayers->forPage($page, self::PER_PAGE)->values(),
            $allPlayers->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $nflTeams = NflTeam::orderBy('name')->get();

        $myBudget = $myTeam ? $myTeam->fabBudgetRemaining($season) : null;

        return view('players.index', compact('players', 'nflTeams', 'season', 'myBudget', 'ownership'));
    }

    public function create()
    {
        return view('players.form', [
            'player' => new Player(),
            'nflTeams' => NflTeam::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $player = Player::create($validated);

        return redirect()->route('players.index')->with('status', "Added {$player->name} to the player pool.");
    }

    public function edit(Player $player)
    {
        return view('players.form', [
            'player' => $player,
            'nflTeams' => NflTeam::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Player $player)
    {
        $validated = $this->validated($request);

        $player->update($validated);

        return redirect()->route('players.index')->with('status', "Updated {$player->name}.");
    }

    /**
     * Players are only ever removed from the local pool directly — a
     * rostered player has to be dropped through the normal roster flow
     * first, so that drop still gets logged to the Transaction feed like
     * any other. Draft picks and transaction/waiver history are foreign-key
     * protected, so a player with that kind of history can't be deleted at
     * all; the commissioner can still edit their name/position/team instead.
     */
    public function destroy(Player $player)
    {
        if ($player->rosterPlayer) {
            return back()->withErrors(['player' => "{$player->name} is on a roster — drop them from their team first."]);
        }

        try {
            $player->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withErrors(['player' => "Can't remove {$player->name} — they have draft or transaction history on record. Edit them instead."]);
        }

        return redirect()->route('players.index')->with('status', "Removed {$player->name} from the player pool.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'position' => ['required', 'in:'.implode(',', Player::POSITIONS)],
            'nfl_team_id' => ['nullable', 'exists:nfl_teams,id'],
        ]);
    }
}
