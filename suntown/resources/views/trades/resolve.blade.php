<x-app-layout>
    <x-slot name="header">
        <div class="eyebrow mb-1">Resolve Roster Limit</div>
        <h2 class="font-display text-4xl text-white tracking-wide">
            {{ $trade->proposerTeam->name }} &amp; {{ $trade->recipientTeam->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div class="bg-endzone/10 border border-endzone/30 text-endzone px-4 py-3 rounded-md text-sm font-medium">{{ $errors->first() }}</div>
            @endif

            <div class="card-panel overflow-hidden">
                <div class="p-6">
                    <p class="text-sm text-ink/50 mb-6">This trade would put the team(s) below over the roster limit. Pick who to drop for each before it can go through.</p>

                    <form method="POST" action="{{ route('trades.resolve.store', $trade) }}">
                        @csrf

                        <div class="space-y-8">
                            @foreach ($teams as $entry)
                                <div>
                                    <h3 class="eyebrow mb-3">{{ $entry['team']->name }} &mdash; drop {{ $entry['needed'] }} player{{ $entry['needed'] > 1 ? 's' : '' }}</h3>
                                    <div class="space-y-1">
                                        @foreach ($entry['roster'] as $player)
                                            <label class="flex items-center gap-2 text-sm py-1.5 px-2 rounded hover:bg-chalk/60 cursor-pointer">
                                                <input type="checkbox" name="drops[{{ $entry['team']->id }}][]" value="{{ $player->id }}" class="rounded border-ink/20 text-endzone focus:ring-endzone">
                                                <x-player-link :player="$player" class="font-bold text-ink" />
                                                <span class="text-ink/40 font-mono text-xs">{{ $player->position }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            <x-primary-button type="submit">Confirm &amp; Complete Trade</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
