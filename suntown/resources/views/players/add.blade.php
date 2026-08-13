<x-app-layout>
    <x-slot name="header">
        <div class="eyebrow mb-1">Add Player</div>
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
                        <span class="text-ink/40 text-xs">{{ $player->nflTeam?->abbr ?? 'Free Agent NFL' }}</span>
                    </div>
                    <span class="record-chip">{{ $currentCount }} / {{ $limit }} spots filled</span>
                </div>

                @if (! $needsDrop)
                    <p class="text-sm text-ink/60">
                        {{ $team->name }} has room on the roster — no need to drop anyone.
                    </p>

                    <form method="POST" action="{{ route('players.add.store', $player) }}" class="flex items-center gap-3">
                        @csrf
                        <x-primary-button type="submit">Confirm</x-primary-button>
                        <a href="{{ route('players.index') }}" class="text-xs font-bold uppercase tracking-wide text-ink/40 hover:text-ink">Cancel</a>
                    </form>
                @else
                    <p class="text-sm text-ink/60">
                        {{ $team->name }}'s roster is full. Choose a player to drop to make room for <x-player-link :player="$player" class="font-bold" />.
                    </p>

                    <form method="POST" action="{{ route('players.add.store', $player) }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-ink/50 mb-1">Drop</label>
                            <select name="drop_player_id" required class="w-full border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf">
                                <option value="">Choose a player&hellip;</option>
                                @foreach ($rosterPlayers as $rosterPlayer)
                                    <option value="{{ $rosterPlayer->id }}">
                                        {{ $rosterPlayer->name }} ({{ $rosterPlayer->position }}{{ $rosterPlayer->nflTeam ? ' - '.$rosterPlayer->nflTeam->abbr : '' }}) &mdash; {{ number_format($rosterPlayer->pointsForSeason($season), 1) }} pts this season
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-3">
                            <x-primary-button type="submit">Add &amp; Drop</x-primary-button>
                            <a href="{{ route('players.index') }}" class="text-xs font-bold uppercase tracking-wide text-ink/40 hover:text-ink">Cancel</a>
                        </div>
                    </form>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
