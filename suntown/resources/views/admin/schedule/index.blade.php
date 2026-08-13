<x-app-layout>
    <x-slot name="header">
        <div class="eyebrow mb-1">Commissioner Tools</div>
        <h2 class="font-display text-4xl text-white tracking-wide">Commissioner</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('admin._nav')

            @if (session('status'))
                <div class="bg-turf/10 border border-turf/30 text-turf font-medium px-4 py-3 rounded-md text-sm">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="bg-endzone/10 border border-endzone/30 text-endzone font-medium px-4 py-3 rounded-md text-sm">{{ $errors->first() }}</div>
            @endif

            <div class="card-panel overflow-hidden p-6 space-y-4">
                <h3 class="font-display text-2xl text-ink tracking-wide">Generate Round-Robin Schedule</h3>
                <p class="text-sm text-ink/50">Replaces the entire schedule for the season below with a fresh round-robin (each team plays every other team once per cycle).</p>
                <form method="POST" action="{{ route('admin.schedule.generate') }}" onsubmit="return confirm('This replaces the entire schedule for this season. Continue?')" class="flex items-end gap-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Season</label>
                        <input type="number" name="season" value="{{ $season }}" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-28">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Weeks</label>
                        <input type="number" name="weeks_count" value="14" min="1" max="25" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-20">
                    </div>
                    <x-primary-button type="submit">Generate</x-primary-button>
                </form>
            </div>

            <div class="card-panel overflow-hidden p-6 space-y-4">
                <h3 class="font-display text-2xl text-ink tracking-wide">Add a Matchup Manually</h3>
                <form method="POST" action="{{ route('admin.schedule.store') }}" class="flex items-end gap-3 flex-wrap">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Season</label>
                        <input type="number" name="season" value="{{ $season }}" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-24">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Week</label>
                        <input type="number" name="week" value="1" min="1" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-16">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Home</label>
                        <select name="home_team_id" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf">
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Away</label>
                        <select name="away_team_id" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf">
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-secondary-button type="submit">Add</x-secondary-button>
                </form>
            </div>

            <div class="card-panel overflow-hidden p-6">
                <form method="GET" class="flex items-end gap-3 mb-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Season</label>
                        <input type="number" name="season" value="{{ $season }}" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-28">
                    </div>
                    <x-secondary-button type="submit">View Season</x-secondary-button>
                </form>

                @forelse ($matchups as $week => $weekMatchups)
                    <div class="mb-4">
                        <h4 class="text-xs font-bold uppercase tracking-wide text-ink/50 mb-2">Week {{ $week }}</h4>
                        <ul class="space-y-1">
                            @foreach ($weekMatchups as $matchup)
                                <li class="flex items-center justify-between text-sm border-b border-ink/5 py-1">
                                    <span>{{ $matchup->homeTeam->name }} vs {{ $matchup->awayTeam->name }}</span>
                                    <form method="POST" action="{{ route('admin.schedule.destroy', $matchup) }}" onsubmit="return confirm('Remove this matchup?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-endzone hover:text-red-800">Remove</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <p class="text-sm text-ink/50">No schedule set for {{ $season }} yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
