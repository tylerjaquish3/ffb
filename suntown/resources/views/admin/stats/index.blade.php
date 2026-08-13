<x-app-layout>
    <x-slot name="header">
        <div class="eyebrow mb-1">Commissioner Tools</div>
        <h2 class="font-display text-4xl text-white tracking-wide">Commissioner</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('admin._nav')

            @if (session('status'))
                <div class="bg-turf/10 border border-turf/30 text-turf font-medium px-4 py-3 rounded-md text-sm mb-4">{{ session('status') }}</div>
            @endif

            <div class="card-panel overflow-hidden p-6 space-y-4">
                <h3 class="font-display text-2xl text-ink tracking-wide">Enter Weekly Stats</h3>
                <p class="text-sm text-ink/50">Enter each rostered player's raw stat totals for the week. Fantasy points are calculated automatically from the stat categories' point values.</p>

                @foreach ($statCategories->where('base_points', '!=', 0) as $baseStat)
                    <p class="text-sm text-ink/50">For <span class="font-bold text-ink">{{ $baseStat->label }}</span>, enter the actual value (e.g. points the defense allowed) &mdash; it's scored as a {{ rtrim(rtrim(number_format($baseStat->base_points, 3), '0'), '.') }}-point base that {{ $baseStat->points_per_unit < 0 ? 'decreases' : 'increases' }} as the value rises.</p>
                @endforeach

                <form method="GET" class="flex items-end gap-3 flex-wrap">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Season</label>
                        <input type="number" name="season" value="{{ $season }}" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-24">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Week</label>
                        <input type="number" name="week" value="{{ $week }}" min="1" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-16">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Team</label>
                        <select name="team_id" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf">
                            <option value="">All Teams</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}" @selected($teamId === $team->id)>{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Position</label>
                        <select name="position" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf">
                            <option value="">All</option>
                            @foreach (\App\Models\Player::POSITIONS as $position)
                                <option value="{{ $position }}" @selected(request('position') === $position)>{{ $position }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf">
                    </div>
                    <x-secondary-button type="submit">Filter</x-secondary-button>
                </form>

                <form method="POST" action="{{ route('admin.stats.store') }}">
                    @csrf
                    <input type="hidden" name="season" value="{{ $season }}">
                    <input type="hidden" name="week" value="{{ $week }}">

                    <div class="overflow-x-auto mt-4">
                        <table class="min-w-full text-xs text-left">
                            <thead>
                                <tr class="border-b-2 border-ink/10 text-ink/50">
                                    <th class="py-2 pr-4 sticky left-0 bg-chalk-white">Player</th>
                                    <th class="py-2 pr-4">Pos</th>
                                    <th class="py-2 pr-4">Team</th>
                                    @foreach ($statCategories as $statCategory)
                                        <th class="py-2 pr-2 text-center" title="{{ $statCategory->label }}">{{ $statCategory->code }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($players as $player)
                                    <tr class="border-b border-ink/5">
                                        <td class="py-1 pr-4 font-medium sticky left-0 bg-chalk-white whitespace-nowrap"><x-player-link :player="$player" /></td>
                                        <td class="py-1 pr-4">{{ $player->position }}</td>
                                        <td class="py-1 pr-4 text-ink/50 whitespace-nowrap">{{ $player->rosterPlayer?->team?->name }}</td>
                                        @foreach ($statCategories as $statCategory)
                                            <td class="py-1 pr-2">
                                                <input type="number" step="0.01"
                                                       name="stats[{{ $player->id }}][{{ $statCategory->id }}]"
                                                       value="{{ $player->existingStats[$statCategory->id] ?? '' }}"
                                                       class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-xs w-16 focus:border-turf focus:ring-turf">
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($players->isEmpty())
                        <p class="text-sm text-ink/50 mt-4">No rostered players match these filters.</p>
                    @else
                        <div class="mt-4">
                            <x-primary-button type="submit">Save Stats</x-primary-button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
