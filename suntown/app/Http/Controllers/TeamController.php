<?php

namespace App\Http\Controllers;

use App\Models\Lineup;
use App\Models\LeagueSetting;
use App\Models\Player;
use App\Models\RosterPlayer;
use App\Models\RosterPosition;
use App\Models\StatCategory;
use App\Models\Team;
use App\Models\Trade;
use App\Models\Transaction;
use App\Models\WaiverClaim;
use App\Support\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    /**
     * Kickers and defenses score on entirely different stat lines than
     * offensive skill players, so the roster splits into its own table per
     * group rather than one table with mostly-blank columns.
     */
    private const POSITION_GROUPS = [
        'offense' => ['label' => 'Offense', 'positions' => ['QB', 'RB', 'WR', 'TE']],
        'kicker' => ['label' => 'Kickers', 'positions' => ['K']],
        'defense' => ['label' => 'Defense / Special Teams', 'positions' => ['DEF']],
    ];

    public function show(Request $request, Team $team)
    {
        $season = $request->integer('season', now()->year);

        Trade::processDueReviews();
        WaiverClaim::processDueWaivers($season);

        $week = $request->integer('week', Season::currentWeek($season));

        $canEdit = $request->user()->id === $team->user_id || $request->user()->is_commissioner;
        $myTeam = $request->user()->team;
        $canProposeTrade = $myTeam && $myTeam->id !== $team->id && ! LeagueSetting::current()->tradeDeadlinePassed();
        $pendingTrades = $canEdit ? $team->pendingTrades() : collect();

        $rows = $this->buildPlayerRows($team, $season, $week);
        $statCategories = StatCategory::orderBy('sort_order')->get();

        $sections = collect(self::POSITION_GROUPS)->map(fn (array $group) => [
            'label' => $group['label'],
            'rows' => $rows->filter(fn (array $row) => in_array($row['player']->position, $group['positions'], true))->values(),
            'statCategories' => $statCategories
                ->filter(fn (StatCategory $category) => array_intersect($category->eligible_positions ?? [], $group['positions']))
                ->values(),
        ])->values();

        $matchup = $team->matchupForWeek($season, $week)?->load(['homeTeam', 'awayTeam']);

        return view('teams.show', [
            'team' => $team,
            'sections' => $sections,
            'season' => $season,
            'week' => $week,
            'canEdit' => $canEdit,
            'matchup' => $matchup,
            'canProposeTrade' => $canProposeTrade,
            'pendingTrades' => $pendingTrades,
        ]);
    }

    /**
     * Voluntarily cutting a player loose, independent of adding anyone —
     * the only other way a player leaves a roster is a forced drop (roster
     * full on add, or roster-limit overflow on a trade). Sends them to
     * waivers just the same, since that's derived from the Transaction log.
     */
    public function dropPlayer(Request $request, Team $team, Player $player)
    {
        abort_unless($request->user()->id === $team->user_id || $request->user()->is_commissioner, 403);
        abort_unless($player->rosterPlayer?->team_id === $team->id, 400);

        $season = now()->year;
        if ($player->isLockedForWeek($season, Season::currentWeek($season))) {
            return back()->withErrors(['player' => "{$player->name}'s game has already started — they're locked until waivers clear Tuesday night."]);
        }

        DB::transaction(function () use ($team, $player) {
            RosterPlayer::where('team_id', $team->id)->where('player_id', $player->id)->delete();
            Lineup::where('team_id', $team->id)->where('player_id', $player->id)->delete();

            Transaction::create([
                'type' => Transaction::TYPE_DROP,
                'season' => now()->year,
                'team_id' => $team->id,
                'player_id' => $player->id,
            ]);
        });

        return redirect()->route('teams.show', $team)->with('status', "Dropped {$player->name}.");
    }

    public function update(Request $request, Team $team)
    {
        abort_unless($request->user()->id === $team->user_id || $request->user()->is_commissioner, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
        ]);

        $team->update($validated);

        return back()->with('status', 'Team name updated.');
    }

    public function updateLineup(Request $request, Team $team)
    {
        abort_unless($request->user()->id === $team->user_id || $request->user()->is_commissioner, 403);

        $validated = $request->validate([
            'season' => ['required', 'integer'],
            'week' => ['required', 'integer'],
            'assignments' => ['array'],
            'assignments.*' => ['nullable', 'string'],
        ]);

        $season = $validated['season'];
        $week = $validated['week'];
        $submitted = $validated['assignments'] ?? [];

        $rosterPlayers = $team->players()->get()->keyBy('id');
        $rosterPositions = RosterPosition::where('code', '!=', RosterPosition::BENCH_CODE)->get()->keyBy('id');
        $originalSlots = $this->currentSlotMap($team, $season, $week);

        $lockedPlayers = $rosterPlayers->filter(fn (Player $p) => $p->isLockedForWeek($season, $week));

        $errors = [];

        foreach ($lockedPlayers as $player) {
            $requested = $submitted[$player->id] ?? null;
            if ($requested !== null && $requested !== ($originalSlots[$player->id] ?? '')) {
                $errors[] = "{$player->name}'s game has already started — their lineup slot is locked until waivers clear Tuesday night.";
            }
        }

        if ($errors) {
            return back()->withErrors(['assignments' => implode(' ', $errors)]);
        }

        $assignments = $this->resolveSwaps($originalSlots, $submitted);

        // A locked player's slot can never move, even as the indirect side
        // effect of another manager's swap request — force it back after
        // swap resolution; any conflicting claim then surfaces via the
        // ordinary slot-collision check below.
        foreach ($lockedPlayers as $player) {
            $assignments[$player->id] = $originalSlots[$player->id] ?? '';
        }

        $slotClaims = [];

        foreach ($assignments as $playerId => $value) {
            $player = $rosterPlayers->get((int) $playerId);
            if (! $player || ! $value) {
                continue;
            }

            [$rosterPositionId, $slotIndex] = array_pad(explode('-', $value, 2), 2, null);
            $rosterPosition = $rosterPositions->get((int) $rosterPositionId);

            if (! $rosterPosition || ! $slotIndex || (int) $slotIndex < 1 || (int) $slotIndex > $rosterPosition->slot_count) {
                $errors[] = "{$player->name}'s slot selection was invalid.";

                continue;
            }

            if (! $player->isEligibleFor($rosterPosition)) {
                $errors[] = "{$player->name} isn't eligible for {$rosterPosition->code}.";

                continue;
            }

            $key = "{$rosterPosition->id}-{$slotIndex}";

            if (isset($slotClaims[$key])) {
                $errors[] = "{$slotClaims[$key]->name} and {$player->name} can't both start at {$rosterPosition->code}".($rosterPosition->slot_count > 1 ? " ({$slotIndex})" : '').'.';

                continue;
            }

            $slotClaims[$key] = $player;
        }

        if ($errors) {
            return back()->withErrors(['assignments' => implode(' ', $errors)]);
        }

        // Saving a lineup never changes total roster size — the only way it
        // can push a team over the limit is by moving someone off IR, since
        // IR slots are exempt from the count (see Team::rosterCountForLimit()).
        $irPositionIds = $rosterPositions->filter(fn (RosterPosition $rp) => $rp->isIR())->keys()->all();

        $irCountAfter = collect($slotClaims)
            ->keys()
            ->filter(fn ($key) => in_array((int) explode('-', $key)[0], $irPositionIds, true))
            ->count();

        $limit = RosterPosition::rosterLimit();
        $effectiveCount = $rosterPlayers->count() - $irCountAfter;

        if ($effectiveCount > $limit) {
            return back()->withErrors(['assignments' => "Moving that player off IR would put your roster over the {$limit}-player limit — drop someone first."]);
        }

        DB::transaction(function () use ($team, $season, $week, $slotClaims) {
            Lineup::where('team_id', $team->id)->where('season', $season)->where('week', $week)->delete();

            foreach ($slotClaims as $key => $player) {
                [$rosterPositionId, $slotIndex] = explode('-', $key);

                Lineup::create([
                    'team_id' => $team->id,
                    'season' => $season,
                    'week' => $week,
                    'roster_position_id' => $rosterPositionId,
                    'slot_index' => $slotIndex,
                    'player_id' => $player->id,
                ]);
            }
        });

        return redirect()
            ->route('teams.show', ['team' => $team, 'season' => $season, 'week' => $week])
            ->with('status', 'Lineup updated.');
    }

    /**
     * playerId => "rosterPositionId-slotIndex" (or absent if benched) for
     * whichever week's Lineup rows are actually in effect right now — shared
     * by resolveSwaps() and the lock check, which both need to know a
     * player's current slot before any requested changes are applied.
     */
    private function currentSlotMap(Team $team, int $season, int $week): array
    {
        return Lineup::where('team_id', $team->id)
            ->where('season', $season)
            ->where('week', $team->resolvedLineupWeek($season, $week))
            ->get()
            ->mapWithKeys(fn (Lineup $lineup) => [$lineup->player_id => "{$lineup->roster_position_id}-{$lineup->slot_index}"])
            ->all();
    }

    /**
     * Moving a player into a slot someone else currently holds swaps the two
     * of them rather than erroring, as long as the occupant didn't
     * themselves request a different slot — mirrors how a drag-and-drop
     * lineup builder would behave, without requiring the user to also edit
     * the occupant's own dropdown.
     */
    private function resolveSwaps(array $original, array $assignments): array
    {
        $requested = $assignments;

        foreach ($requested as $playerId => $value) {
            $playerId = (int) $playerId;
            $originalValue = $original[$playerId] ?? '';

            if ($value === '' || $value === $originalValue) {
                continue;
            }

            $occupantId = array_search($value, $original, true);

            if ($occupantId === false || $occupantId === $playerId) {
                continue;
            }

            $occupantRequested = $requested[$occupantId] ?? ($original[$occupantId] ?? '');

            if ($occupantRequested === ($original[$occupantId] ?? '')) {
                $requested[$occupantId] = $originalValue;
            }
        }

        return $requested;
    }

    /**
     * One row per rostered player: their current starting slot (or bench),
     * the slots they're eligible to move into, their points, and this
     * week's real NFL matchup for their team.
     */
    private function buildPlayerRows(Team $team, int $season, int $week)
    {
        // Lineups carry forward unchanged until a manager explicitly saves a
        // week, so the slot assignments actually in effect may come from an
        // earlier week than the one being displayed.
        $lineupsByPlayer = Lineup::where('team_id', $team->id)
            ->where('season', $season)
            ->where('week', $team->resolvedLineupWeek($season, $week))
            ->with('rosterPosition')
            ->get()
            ->keyBy('player_id');

        $rosterPositions = RosterPosition::where('code', '!=', RosterPosition::BENCH_CODE)
            ->orderBy('sort_order')
            ->get();

        return $team->players()->with([
            'nflTeam',
            'weekStats' => fn ($q) => $q->where('season', $season)->where('week', $week),
        ])->get()
            ->map(function (Player $player) use ($lineupsByPlayer, $rosterPositions, $season, $week) {
                $lineup = $lineupsByPlayer->get($player->id);

                $options = $rosterPositions
                    ->filter(fn (RosterPosition $rp) => $player->isEligibleFor($rp))
                    ->flatMap(fn (RosterPosition $rp) => collect(range(1, $rp->slot_count))->map(fn ($i) => [
                        'value' => "{$rp->id}-{$i}",
                        'label' => $rp->slot_count > 1 ? "{$rp->code} ({$i})" : $rp->code,
                        'sort' => $rp->sort_order * 100 + $i,
                    ]))
                    ->sortBy('sort')
                    ->values();

                $game = $player->nflGameForWeek($season, $week);

                return [
                    'player' => $player,
                    'value' => $lineup ? "{$lineup->roster_position_id}-{$lineup->slot_index}" : '',
                    'label' => $lineup ? $lineup->rosterPosition->code.($lineup->rosterPosition->slot_count > 1 ? " ({$lineup->slot_index})" : '') : 'Bench',
                    'options' => $options,
                    'sort' => $lineup ? $lineup->rosterPosition->sort_order * 100 + $lineup->slot_index : 9000 + $player->id,
                    'points' => $player->pointsForWeek($season, $week),
                    'stats' => $player->weekStats->pluck('value', 'stat_category_id'),
                    'locked' => $player->isLockedForWeek($season, $week),
                    'game' => $game ? [
                        'opponent' => $game->opponentFor($player->nfl_team_id),
                        'is_home' => $game->isHomeFor($player->nfl_team_id),
                        'kickoff_at' => $game->kickoff_at,
                    ] : null,
                ];
            })
            ->sortBy('sort')
            ->values();
    }

}
