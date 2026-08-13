<x-app-layout>
    <x-slot name="header">
        <div class="eyebrow mb-1">Commissioner Tools</div>
        <h2 class="font-display text-4xl text-white tracking-wide">Commissioner</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin._nav')

            @if (session('status'))
                <div class="bg-turf/10 border border-turf/30 text-turf font-medium px-4 py-3 rounded-md text-sm mb-4">{{ session('status') }}</div>
            @endif

            @if (session('importOutput'))
                <pre class="bg-ink text-chalk-white text-xs p-4 rounded-md mb-4 overflow-x-auto">{{ session('importOutput') }}</pre>
            @endif

            <div class="card-panel overflow-hidden p-6 space-y-4 mb-6">
                <h3 class="font-display text-2xl text-ink tracking-wide">Player Pool</h3>
                <p class="text-sm text-ink/50">
                    The full NFL player list lives in this app's own <code>players</code> table, synced once from
                    <code>../draft</code> — every player, drafted or not, so the Players page shows real free agents.
                </p>

                <p class="text-sm font-mono">
                    <span class="record-chip">{{ $localPlayerCount }} synced here</span>
                    @if ($preview && isset($preview['source_player_count']))
                        <span class="text-ink/40 ml-2">of {{ $preview['source_player_count'] }} in the source database</span>
                    @endif
                </p>

                <form method="POST" action="{{ route('admin.draft-import.sync-players') }}">
                    @csrf
                    <x-primary-button type="submit">Sync Player Pool</x-primary-button>
                </form>
            </div>

            <div class="card-panel overflow-hidden p-6 space-y-4 mb-6">
                <h3 class="font-display text-2xl text-ink tracking-wide">NFL Schedule</h3>
                <p class="text-sm text-ink/50">
                    Real NFL game dates/times from <code>../ffb</code>, stored here so team pages can show each
                    player's opponent and kickoff time — and so the app knows which week is currently up next.
                </p>

                <p class="text-sm font-mono">
                    <span class="record-chip">{{ $localGameCount }} games synced</span>
                    <span class="text-ink/40 ml-2">for the {{ $season }} season</span>
                </p>

                <form method="POST" action="{{ route('admin.draft-import.sync-schedule') }}" class="flex items-end gap-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Season</label>
                        <input type="number" name="season" value="{{ $season }}" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-28">
                    </div>
                    <x-primary-button type="submit">Sync NFL Schedule</x-primary-button>
                </form>
            </div>

            <div class="card-panel overflow-hidden p-6 space-y-4 mb-6">
                <h3 class="font-display text-2xl text-ink tracking-wide">Injury Report</h3>
                <p class="text-sm text-ink/50">
                    Weekly injury statuses from the Sportradar NFL Official API, stored on each player and shown as a
                    small badge on the Players page. Also runs automatically every few hours.
                </p>

                <p class="text-sm font-mono">
                    <span class="record-chip">{{ $injuredPlayerCount }} players currently flagged</span>
                </p>

                <form method="POST" action="{{ route('admin.draft-import.sync-injuries') }}" class="flex items-end gap-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Season</label>
                        <input type="number" name="season" value="{{ $season }}" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-28">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Week</label>
                        <input type="number" name="week" value="{{ $week }}" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-20">
                    </div>
                    <x-primary-button type="submit">Sync Injury Report</x-primary-button>
                </form>
            </div>

            <div class="card-panel overflow-hidden p-6 space-y-4">
                <h3 class="font-display text-2xl text-ink tracking-wide">Draft Import</h3>
                <p class="text-sm text-ink/50">
                    Pulls draft picks straight from the <code>../draft</code> project's database and fills each team's roster.
                    This is a full rebuild for the season entered — safe to re-run as a live draft continues.
                </p>

                <form method="GET" class="flex items-end gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Season</label>
                        <input type="number" name="season" value="{{ $season }}" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-28">
                    </div>
                    <x-secondary-button type="submit">Preview</x-secondary-button>
                </form>

                @if ($preview)
                    @if (isset($preview['error']))
                        <div class="bg-endzone/10 border border-endzone/30 text-endzone font-medium px-4 py-3 rounded-md text-sm">
                            Could not read the draft source database: {{ $preview['error'] }}
                        </div>
                    @else
                        <p class="text-sm">
                            <strong>{{ $preview['pick_count'] }}</strong> draft picks found for season {{ $season }} in the source database.
                        </p>
                    @endif
                @endif

                <form method="POST" action="{{ route('admin.draft-import.store') }}" onsubmit="return confirm('Import season {{ $season }}? This replaces all current rosters with the draft results for this season.')">
                    @csrf
                    <input type="hidden" name="season" value="{{ $season }}">
                    <x-primary-button type="submit">Import Season {{ $season }}</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
