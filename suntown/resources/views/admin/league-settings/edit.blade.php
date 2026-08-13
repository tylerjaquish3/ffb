<x-app-layout>
    <x-slot name="header">
        <div class="eyebrow mb-1">Commissioner Tools</div>
        <h2 class="font-display text-4xl text-white tracking-wide">Commissioner</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @include('admin._nav')

            @if (session('status'))
                <div class="bg-turf/10 border border-turf/30 text-turf font-medium px-4 py-3 rounded-md text-sm mb-4">{{ session('status') }}</div>
            @endif

            <div class="card-panel overflow-hidden p-6 space-y-4">
                <h3 class="font-display text-2xl text-ink tracking-wide">League Settings</h3>
                <p class="text-sm text-ink/50">These apply league-wide, starting immediately — they don't affect trades or waiver periods already in progress.</p>

                <form method="POST" action="{{ route('admin.league-settings.update') }}" class="space-y-6 max-w-lg">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50 mb-1">Trade Review Period</label>
                        <p class="text-xs text-ink/40 mb-2">How long a mutually-accepted trade stays open to league veto votes before it goes through.</p>
                        <select name="trade_review_days" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-40">
                            @foreach ([0, 1, 2, 3] as $days)
                                <option value="{{ $days }}" @selected($setting->trade_review_days === $days)>{{ $days }} day{{ $days === 1 ? '' : 's' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50 mb-1">Waiver Period</label>
                        <p class="text-xs text-ink/40 mb-2">How long a dropped player is locked to bid-only waivers before becoming a plain free agent.</p>
                        <select name="waiver_days" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-40">
                            @foreach ([0, 1, 2, 3, 4] as $days)
                                <option value="{{ $days }}" @selected($setting->waiver_days === $days)>{{ $days }} day{{ $days === 1 ? '' : 's' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50 mb-1">Starting FAB Budget</label>
                        <p class="text-xs text-ink/40 mb-2">What every manager's Free Agent Acquisition Budget resets to at the start of the season.</p>
                        <input type="number" name="starting_fab_budget" value="{{ $setting->starting_fab_budget }}" min="0" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-40">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50 mb-1">Trade Deadline</label>
                        <p class="text-xs text-ink/40 mb-2">Last day managers can propose or counter a trade. Trades already under review can still execute after this date. Leave blank for no deadline.</p>
                        <input type="date" name="trade_deadline" value="{{ $setting->trade_deadline?->toDateString() }}" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-40">
                    </div>

                    <x-primary-button type="submit">Save Settings</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
