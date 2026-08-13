<x-app-layout>
    <x-slot name="header">
        <div class="eyebrow mb-1">Free Agents &amp; Rosters</div>
        <h2 class="font-display text-4xl text-white tracking-wide">
            Players
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="bg-turf/10 border border-turf/30 text-turf px-4 py-3 rounded-md text-sm font-medium">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="bg-endzone/10 border border-endzone/30 text-endzone px-4 py-3 rounded-md text-sm font-medium">{{ $errors->first() }}</div>
            @endif

            <div class="flex justify-end items-center gap-3">
                @if (Auth::user()->is_commissioner)
                    <a href="{{ route('players.create') }}">
                        <x-secondary-button type="button">+ Add Player</x-secondary-button>
                    </a>
                @endif
                @if (! is_null($myBudget))
                    <span class="record-chip">FAB Budget: ${{ $myBudget }}</span>
                @endif
            </div>

            <div class="card-panel overflow-hidden">
                <div class="p-6">

                    <form method="GET" class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name..."
                               class="col-span-2 sm:col-span-1 border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf">

                        <select name="position" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf">
                            <option value="">All Positions</option>
                            @foreach (\App\Models\Player::POSITIONS as $position)
                                <option value="{{ $position }}" @selected(request('position') === $position)>{{ $position }}</option>
                            @endforeach
                        </select>

                        <select name="nfl_team_id" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf">
                            <option value="">All NFL Teams</option>
                            @foreach ($nflTeams as $nflTeam)
                                <option value="{{ $nflTeam->id }}" @selected((int) request('nfl_team_id') === $nflTeam->id)>{{ $nflTeam->abbr }}</option>
                            @endforeach
                        </select>

                        <select name="ownership" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf">
                            <option value="" @selected($ownership === '')>Owned + Free Agents</option>
                            <option value="owned" @selected($ownership === 'owned')>Owned Only</option>
                            <option value="free_agent" @selected($ownership === 'free_agent')>Free Agents Only</option>
                        </select>

                        <x-secondary-button type="submit">Filter</x-secondary-button>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-left">
                            <thead>
                                <tr class="border-b-2 border-ink/10 text-ink/50 font-sans font-bold uppercase text-xs tracking-wide">
                                    <th class="py-2 pr-4">Player</th>
                                    <th class="py-2 pr-4">Pos</th>
                                    <th class="py-2 pr-4">NFL Team</th>
                                    <th class="py-2 pr-4 text-right">{{ $season }} Pts</th>
                                    <th class="py-2 pr-4">Fantasy Team</th>
                                    <th class="py-2 pr-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($players as $player)
                                    <tr class="border-b border-ink/5 hover:bg-chalk/60">
                                        <td class="py-2.5 pr-4 font-bold">
                                            <x-player-link :player="$player" class="font-bold" />
                                            @if ($player->injury_status)
                                                <span
                                                    class="ml-1 text-endzone font-bold text-[10px] uppercase align-super"
                                                    title="{{ $player->injury_status }}{{ $player->injury_description ? ' — '.$player->injury_description : '' }}"
                                                >{{ $player->injuryBadgeLabel() }}</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 pr-4 text-ink/60 font-mono text-xs">{{ $player->position }}</td>
                                        <td class="py-2.5 pr-4 text-ink/50">
                                            @if ($player->nflTeam)
                                                {{ $player->nflTeam->abbr }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-2.5 pr-4 text-right font-mono tabular-nums">{{ number_format($player->seasonPoints, 1) }}</td>
                                        <td class="py-2.5 pr-4">
                                            @if ($player->rosterPlayer?->team)
                                                <a href="{{ route('teams.show', $player->rosterPlayer->team) }}" class="text-turf hover:text-turf-light font-bold transition">
                                                    {{ $player->rosterPlayer->team->name }}
                                                </a>
                                            @elseif ($player->waiverLockUntil)
                                                <span class="text-ink/30 font-bold text-xs uppercase tracking-wide">On Waivers &middot; clears {{ $player->waiverLockUntil->format('M j, g:ia') }}</span>
                                            @else
                                                <span class="text-ink/30 font-bold text-xs uppercase tracking-wide">Free Agent</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 pr-4 text-right whitespace-nowrap">
                                            @if (! $player->rosterPlayer)
                                                @if ($player->waiverLockUntil)
                                                    <a href="{{ route('waivers.create', $player) }}" class="text-xs font-bold uppercase tracking-wide text-turf hover:text-turf-light transition">
                                                        {{ $player->myBidAmount !== null ? 'Bid: $'.$player->myBidAmount : 'Bid' }}
                                                    </a>
                                                @else
                                                    <a href="{{ route('players.add.create', $player) }}" class="text-xs font-bold uppercase tracking-wide text-turf hover:text-turf-light transition">+ Add</a>
                                                @endif
                                            @endif
                                            @if (Auth::user()->is_commissioner)
                                                <a href="{{ route('players.edit', $player) }}" class="text-xs font-bold uppercase tracking-wide text-ink/40 hover:text-ink transition ml-3">Edit</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $players->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
