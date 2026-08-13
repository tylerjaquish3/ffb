<x-app-layout>
    <x-slot name="header">
        <div class="eyebrow mb-1">Waiver Bid</div>
        <h2 class="font-display text-4xl text-white tracking-wide">
            <x-player-link :player="$player" class="text-white" />
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div class="bg-endzone/10 border border-endzone/30 text-endzone px-4 py-3 rounded-md text-sm font-medium">{{ $errors->first() }}</div>
            @endif

            <div class="card-panel overflow-hidden p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        @if ($player->nflTeam)
                            <span class="team-dot" style="background-color: {{ $player->nflTeam->primary_color ?? '#999' }}"></span>
                        @endif
                        <x-player-link :player="$player" class="font-bold" />
                        <span class="text-ink/40 font-mono text-xs">{{ $player->position }}</span>
                    </div>
                    <span class="record-chip">On waivers until {{ $clearsAt->format('M j, g:ia') }}</span>
                </div>

                <p class="text-sm text-ink/60">
                    <x-player-link :player="$player" class="font-bold text-ink" /> is locked to blind waiver bidding until the lock lifts. Bids are private — you won't see anyone else's. Highest bid wins; ties go to whoever has the worse record.
                </p>

                <form method="POST" action="{{ route('waivers.store', $player) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50 mb-1">Your Bid ($)</label>
                        <input type="number" name="amount" min="0" max="{{ $remainingBudget }}" value="{{ old('amount', $existingClaim->amount ?? 0) }}" required
                            class="w-32 border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf">
                        <p class="text-xs text-ink/40 mt-1">You have ${{ $remainingBudget }} in uncommitted FAB budget{{ $existingClaim ? " (this bid's current \${$existingClaim->amount} is included)" : '' }}.</p>
                    </div>

                    @if ($needsDrop)
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-ink/50 mb-1">If This Bid Wins, Drop</label>
                            <p class="text-xs text-ink/40 mb-2">Your roster is full ({{ $currentCount }}/{{ $limit }}) — pick who to drop if you win <x-player-link :player="$player" class="font-bold" />.</p>
                            <select name="drop_player_id" required class="w-full border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf">
                                <option value="">Choose a player&hellip;</option>
                                @foreach ($rosterPlayers as $rosterPlayer)
                                    <option value="{{ $rosterPlayer->id }}" @selected(old('drop_player_id', $existingClaim->drop_player_id ?? null) == $rosterPlayer->id)>
                                        {{ $rosterPlayer->name }} ({{ $rosterPlayer->position }}{{ $rosterPlayer->nflTeam ? ' - '.$rosterPlayer->nflTeam->abbr : '' }}) &mdash; {{ number_format($rosterPlayer->pointsForSeason($season), 1) }} pts this season
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex items-center gap-3">
                        <x-primary-button type="submit">{{ $existingClaim ? 'Update Bid' : 'Place Bid' }}</x-primary-button>
                        <a href="{{ route('players.index') }}" class="text-xs font-bold uppercase tracking-wide text-ink/40 hover:text-ink">Cancel</a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
