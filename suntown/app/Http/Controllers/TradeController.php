<?php

namespace App\Http\Controllers;

use App\Models\LeagueSetting;
use App\Models\Player;
use App\Models\RosterPosition;
use App\Models\Team;
use App\Models\Trade;
use App\Support\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TradeController extends Controller
{
    public function create(Request $request, Team $team)
    {
        $myTeam = $request->user()->team;
        abort_unless($myTeam, 403);
        abort_if($myTeam->id === $team->id, 400);

        if (LeagueSetting::current()->tradeDeadlinePassed()) {
            return redirect()->route('teams.show', $team)->withErrors(['trade' => 'The trade deadline has passed — no new trades can be proposed.']);
        }

        $theirPlayers = $team->players()->with('nflTeam')->orderBy('position')->orderBy('name')->get();

        $preselectedPlayer = $request->integer('player');
        $preselectedTheir = $preselectedPlayer && $theirPlayers->contains('id', $preselectedPlayer)
            ? [$preselectedPlayer]
            : [];

        return view('trades.create', [
            'myTeam' => $myTeam,
            'theirTeam' => $team,
            'myPlayers' => $myTeam->players()->with('nflTeam')->orderBy('position')->orderBy('name')->get(),
            'theirPlayers' => $theirPlayers,
            'preselectedMy' => [],
            'preselectedTheir' => $preselectedTheir,
            'parentTrade' => null,
        ]);
    }

    /**
     * Reopens the propose-trade form from the other side, pre-filled with
     * the same players mirrored, as a starting point for a counter offer.
     */
    public function counter(Request $request, Trade $trade)
    {
        abort_unless($trade->isPending(), 400);

        $myTeam = $request->user()->team;
        abort_unless($myTeam && $trade->involvesTeam($myTeam->id), 403);

        if (LeagueSetting::current()->tradeDeadlinePassed()) {
            return redirect()->route('trades.show', $trade)->withErrors(['trade' => 'The trade deadline has passed — no new trades can be proposed.']);
        }

        $theirTeam = Team::findOrFail($trade->otherTeamId($myTeam->id));

        $trade->loadMissing('items');

        return view('trades.create', [
            'myTeam' => $myTeam,
            'theirTeam' => $theirTeam,
            'myPlayers' => $myTeam->players()->with('nflTeam')->orderBy('position')->orderBy('name')->get(),
            'theirPlayers' => $theirTeam->players()->with('nflTeam')->orderBy('position')->orderBy('name')->get(),
            'preselectedMy' => $trade->itemsFrom($myTeam->id)->pluck('player_id')->all(),
            'preselectedTheir' => $trade->itemsFrom($theirTeam->id)->pluck('player_id')->all(),
            'parentTrade' => $trade,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_team_id' => ['required', 'integer', 'exists:teams,id'],
            'parent_trade_id' => ['nullable', 'integer', 'exists:trades,id'],
            'my_players' => ['array'],
            'my_players.*' => ['integer', 'exists:players,id'],
            'their_players' => ['array'],
            'their_players.*' => ['integer', 'exists:players,id'],
        ]);

        $myTeam = $request->user()->team;
        abort_unless($myTeam, 403);

        if (LeagueSetting::current()->tradeDeadlinePassed()) {
            return back()->withErrors(['trade' => 'The trade deadline has passed — no new trades can be proposed.'])->withInput();
        }

        $recipientTeam = Team::findOrFail($validated['recipient_team_id']);
        abort_if($recipientTeam->id === $myTeam->id, 400);

        $myPlayers = $validated['my_players'] ?? [];
        $theirPlayers = $validated['their_players'] ?? [];

        if (empty($myPlayers) || empty($theirPlayers)) {
            return back()->withErrors(['trade' => 'Pick at least one player from each team.'])->withInput();
        }

        $myOwnedCount = $myTeam->players()->whereIn('players.id', $myPlayers)->count();
        $theirOwnedCount = $recipientTeam->players()->whereIn('players.id', $theirPlayers)->count();

        if ($myOwnedCount !== count($myPlayers) || $theirOwnedCount !== count($theirPlayers)) {
            return back()->withErrors(['trade' => 'One of the selected players is no longer on that roster.'])->withInput();
        }

        $locked = $this->lockedPlayersIn(array_merge($myPlayers, $theirPlayers));
        if ($locked->isNotEmpty()) {
            $names = $locked->pluck('name')->join(', ', ' and ');
            $verb = $locked->count() > 1 ? 'have' : 'has';

            return back()->withErrors(['trade' => "{$names} {$verb} a game already underway — locked players can't be traded until waivers clear Tuesday night."])->withInput();
        }

        $parentTrade = null;
        if (! empty($validated['parent_trade_id'])) {
            $candidate = Trade::find($validated['parent_trade_id']);
            if ($candidate && $candidate->isPending() && $candidate->involvesTeam($myTeam->id)) {
                $parentTrade = $candidate;
            }
        }

        $trade = DB::transaction(function () use ($myTeam, $recipientTeam, $myPlayers, $theirPlayers, $parentTrade) {
            $trade = Trade::create([
                'season' => now()->year,
                'proposer_team_id' => $myTeam->id,
                'recipient_team_id' => $recipientTeam->id,
                'status' => Trade::STATUS_PENDING,
                'parent_trade_id' => $parentTrade?->id,
            ]);

            foreach ($myPlayers as $playerId) {
                $trade->items()->create(['team_id' => $myTeam->id, 'player_id' => $playerId]);
            }

            foreach ($theirPlayers as $playerId) {
                $trade->items()->create(['team_id' => $recipientTeam->id, 'player_id' => $playerId]);
            }

            if ($parentTrade) {
                $parentTrade->update(['status' => Trade::STATUS_COUNTERED, 'responded_at' => now()]);
            }

            return $trade;
        });

        return redirect()->route('trades.show', $trade)->with('status', 'Trade proposed.');
    }

    public function show(Request $request, Trade $trade)
    {
        Trade::processDueReviews();
        $trade->refresh();

        $this->authorizeParty($request, $trade);

        $trade->load(['proposerTeam.user', 'recipientTeam.user', 'items.player.nflTeam', 'parentTrade', 'counterTrade', 'vetoes']);

        return view('trades.show', [
            'trade' => $trade,
            'proposerItems' => $trade->swapItemsFrom($trade->proposer_team_id),
            'recipientItems' => $trade->swapItemsFrom($trade->recipient_team_id),
            'forcedDrops' => $trade->forcedDrops(),
            'canRespond' => $trade->isPending() && $this->canRespond($request, $trade),
            'canCancel' => $trade->isPending() && $this->canCancel($request, $trade),
            'canVeto' => $trade->isUnderReview() && $this->canVeto($request, $trade),
            'vetoCount' => $trade->vetoes->count(),
            'tradeDeadlinePassed' => LeagueSetting::current()->tradeDeadlinePassed(),
        ]);
    }

    public function accept(Request $request, Trade $trade)
    {
        $this->authorizeRespond($request, $trade);
        abort_unless($trade->isPending(), 400);

        $trade->loadMissing('items');

        $locked = $this->lockedPlayersIn($trade->swapItemsFrom($trade->proposer_team_id)->pluck('player_id')->merge($trade->swapItemsFrom($trade->recipient_team_id)->pluck('player_id')));
        if ($locked->isNotEmpty()) {
            $names = $locked->pluck('name')->join(', ', ' and ');
            $verb = $locked->count() > 1 ? 'have' : 'has';

            return redirect()->route('trades.show', $trade)->withErrors(['trade' => "{$names} {$verb} a game already underway — wait until waivers clear Tuesday night to accept."]);
        }

        if ($this->overflowFor($trade)) {
            return redirect()->route('trades.resolve', $trade);
        }

        $this->beginReview($trade);

        return redirect()->route('trades.show', $trade)->with('status', $this->acceptedStatusMessage($trade));
    }

    public function decline(Request $request, Trade $trade)
    {
        $this->authorizeRespond($request, $trade);
        abort_unless($trade->isPending(), 400);

        $trade->update(['status' => Trade::STATUS_DECLINED, 'responded_at' => now()]);

        return redirect()->route('trades.show', $trade)->with('status', 'Trade declined.');
    }

    public function cancel(Request $request, Trade $trade)
    {
        abort_unless($this->canCancel($request, $trade), 403);
        abort_unless($trade->isPending(), 400);

        $trade->update(['status' => Trade::STATUS_CANCELLED, 'responded_at' => now()]);

        return redirect()->route('trades.show', $trade)->with('status', 'Trade cancelled.');
    }

    /**
     * Any manager not party to the trade can vote to veto it while it's
     * under review. Enough votes kill the trade outright — it never
     * executes, regardless of how much of the review window is left.
     */
    public function veto(Request $request, Trade $trade)
    {
        Trade::processDueReviews();
        $trade->refresh();

        abort_unless($trade->isUnderReview(), 400);
        abort_unless($this->canVeto($request, $trade), 403);

        $trade->vetoes()->create(['team_id' => $request->user()->team->id]);

        if ($trade->vetoes()->count() >= Trade::VETO_THRESHOLD) {
            $trade->update(['status' => Trade::STATUS_VETOED]);

            return redirect()->route('trades.show', $trade)->with('status', 'Trade vetoed by the league.');
        }

        return back()->with('status', 'Veto vote recorded.');
    }

    /**
     * A trade that pushes either team over the roster limit needs that
     * team to pick who to drop before it can go through.
     */
    public function resolve(Request $request, Trade $trade)
    {
        $this->authorizeParty($request, $trade);
        abort_unless($trade->isPending(), 400);

        $trade->loadMissing('items');

        $locked = $this->lockedPlayersIn($trade->swapItemsFrom($trade->proposer_team_id)->pluck('player_id')->merge($trade->swapItemsFrom($trade->recipient_team_id)->pluck('player_id')));
        if ($locked->isNotEmpty()) {
            $names = $locked->pluck('name')->join(', ', ' and ');
            $verb = $locked->count() > 1 ? 'have' : 'has';

            return redirect()->route('trades.show', $trade)->withErrors(['trade' => "{$names} {$verb} a game already underway — wait until waivers clear Tuesday night to continue."]);
        }

        $overflow = $this->overflowFor($trade);

        if (empty($overflow)) {
            return redirect()->route('trades.show', $trade);
        }

        $season = now()->year;
        $week = Season::currentWeek($season);

        $teams = [];
        foreach ($overflow as $teamId => $needed) {
            $team = Team::find($teamId);
            $outgoingIds = $trade->items->where('team_id', $teamId)->pluck('player_id')->all();
            $incomingIds = $trade->items->where('team_id', '!=', $teamId)->pluck('player_id')->all();

            $remaining = $team->players()->with('nflTeam')->whereNotIn('players.id', $outgoingIds)->get()
                ->reject(fn (Player $p) => $p->isLockedForWeek($season, $week));
            $incoming = Player::whereIn('id', $incomingIds)->with('nflTeam')->get();

            $teams[] = [
                'team' => $team,
                'needed' => $needed,
                'roster' => $remaining->concat($incoming)->sortBy('name')->values(),
            ];
        }

        return view('trades.resolve', compact('trade', 'teams'));
    }

    public function resolveStore(Request $request, Trade $trade)
    {
        $this->authorizeParty($request, $trade);
        abort_unless($trade->isPending(), 400);

        $trade->loadMissing('items');

        $locked = $this->lockedPlayersIn($trade->swapItemsFrom($trade->proposer_team_id)->pluck('player_id')->merge($trade->swapItemsFrom($trade->recipient_team_id)->pluck('player_id')));
        if ($locked->isNotEmpty()) {
            $names = $locked->pluck('name')->join(', ', ' and ');
            $verb = $locked->count() > 1 ? 'have' : 'has';

            return redirect()->route('trades.show', $trade)->withErrors(['trade' => "{$names} {$verb} a game already underway — wait until waivers clear Tuesday night to continue."]);
        }

        $overflow = $this->overflowFor($trade);
        abort_if(empty($overflow), 400);

        $validated = $request->validate([
            'drops' => ['array'],
            'drops.*' => ['array'],
            'drops.*.*' => ['integer', 'exists:players,id'],
        ]);

        $drops = $validated['drops'] ?? [];

        foreach ($overflow as $teamId => $needed) {
            $picked = $drops[$teamId] ?? [];
            if (count($picked) !== $needed) {
                $team = Team::find($teamId);

                return back()->withErrors(['drops' => "{$team->name} needs to drop exactly {$needed} player(s) for this trade to go through."]);
            }
        }

        $lockedDrops = $this->lockedPlayersIn(collect($drops)->flatten());
        if ($lockedDrops->isNotEmpty()) {
            $names = $lockedDrops->pluck('name')->join(', ', ' and ');
            $verb = $lockedDrops->count() > 1 ? 'have' : 'has';

            return back()->withErrors(['drops' => "{$names} {$verb} a game already underway — pick a different player to drop."]);
        }

        foreach ($drops as $teamId => $playerIds) {
            foreach ($playerIds as $playerId) {
                $trade->items()->create(['team_id' => $teamId, 'player_id' => $playerId, 'is_forced_drop' => true]);
            }
        }

        $this->beginReview($trade);

        return redirect()->route('trades.show', $trade)->with('status', $this->acceptedStatusMessage($trade));
    }

    /**
     * Whichever of the given players currently have their game-start lock
     * active — used to block proposing, accepting, or resolving a trade
     * around a player who can't be moved right now.
     */
    private function lockedPlayersIn(iterable $playerIds): \Illuminate\Support\Collection
    {
        $season = now()->year;
        $week = Season::currentWeek($season);

        return Player::whereIn('id', $playerIds)->get()
            ->filter(fn (Player $p) => $p->isLockedForWeek($season, $week))
            ->values();
    }

    /**
     * A trade under review is open to the whole league to view (and vote to
     * veto) — everything else (pending negotiation, resolved history) stays
     * restricted to the two teams involved and the commissioner.
     */
    private function authorizeParty(Request $request, Trade $trade): void
    {
        if ($trade->isUnderReview()) {
            return;
        }

        $myTeam = $request->user()->team;
        abort_unless($request->user()->is_commissioner || ($myTeam && $trade->involvesTeam($myTeam->id)), 403);
    }

    private function authorizeRespond(Request $request, Trade $trade): void
    {
        abort_unless($this->canRespond($request, $trade), 403);
    }

    /**
     * Only the recipient (or the commissioner standing in for them) can
     * accept/decline — critically, this must NOT fall back to "or the
     * commissioner" when the commissioner's own team is the proposer, or a
     * commissioner-manager could approve their own trade proposal.
     */
    private function canRespond(Request $request, Trade $trade): bool
    {
        $myTeam = $request->user()->team;

        if ($myTeam && $myTeam->id === $trade->proposer_team_id) {
            return false;
        }

        return $request->user()->is_commissioner || ($myTeam && $myTeam->id === $trade->recipient_team_id);
    }

    /**
     * Mirror of canRespond() for the proposer's side: only the proposer (or
     * the commissioner standing in for them) can cancel, and never the
     * recipient — even a commissioner-manager who is the recipient.
     */
    private function canCancel(Request $request, Trade $trade): bool
    {
        $myTeam = $request->user()->team;

        if ($myTeam && $myTeam->id === $trade->recipient_team_id) {
            return false;
        }

        return $request->user()->is_commissioner || ($myTeam && $myTeam->id === $trade->proposer_team_id);
    }

    /**
     * Any team not party to the trade gets one veto vote — including the
     * commissioner's own team, but never on a trade their own team is in.
     */
    private function canVeto(Request $request, Trade $trade): bool
    {
        $myTeam = $request->user()->team;

        if (! $myTeam || $trade->involvesTeam($myTeam->id)) {
            return false;
        }

        return ! $trade->vetoes->pluck('team_id')->contains($myTeam->id);
    }

    /**
     * Team id => number of players that team must drop for the trade to fit
     * under the roster limit. Only teams that would go over appear here.
     */
    private function overflowFor(Trade $trade): array
    {
        $limit = RosterPosition::rosterLimit();
        $overflow = [];

        foreach ([$trade->proposer_team_id, $trade->recipient_team_id] as $teamId) {
            $current = Team::find($teamId)->rosterCountForLimit($trade->season);
            $outgoing = $trade->items->where('team_id', $teamId)->where('is_forced_drop', false)->count();
            $incoming = $trade->items->where('team_id', '!=', $teamId)->where('is_forced_drop', false)->count();

            $needed = max(0, ($current - $outgoing + $incoming) - $limit);

            if ($needed > 0) {
                $overflow[$teamId] = $needed;
            }
        }

        return $overflow;
    }

    /**
     * Moves a trade from "recipient just said yes" to either immediately
     * executed (0-day review) or open for league veto votes.
     */
    private function beginReview(Trade $trade): void
    {
        $days = LeagueSetting::current()->trade_review_days;

        $trade->update(['responded_at' => now()]);

        if ($days === 0) {
            $trade->execute();

            return;
        }

        $trade->update([
            'status' => Trade::STATUS_UNDER_REVIEW,
            'review_ends_at' => now()->addDays($days),
        ]);
    }

    private function acceptedStatusMessage(Trade $trade): string
    {
        return $trade->isUnderReview()
            ? 'Trade accepted — now open to league veto votes.'
            : 'Trade accepted.';
    }
}
