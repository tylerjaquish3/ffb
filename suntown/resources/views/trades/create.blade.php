<x-app-layout>
    <x-slot name="header">
        <div class="eyebrow mb-1">{{ $parentTrade ? 'Counter Offer' : 'Propose Trade' }}</div>
        <h2 class="font-display text-4xl text-white tracking-wide">
            {{ $myTeam->name }} &amp; {{ $theirTeam->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div class="bg-endzone/10 border border-endzone/30 text-endzone px-4 py-3 rounded-md text-sm font-medium">{{ $errors->first() }}</div>
            @endif

            <div class="card-panel overflow-hidden">
                <div class="p-6">
                    <p class="text-sm text-ink/50 mb-6">Check the players moving each direction, then send the offer. If it puts either roster over the limit, whoever's over will pick a player to drop once the trade is accepted.</p>

                    <form method="POST" action="{{ route('trades.store') }}">
                        @csrf
                        <input type="hidden" name="recipient_team_id" value="{{ $theirTeam->id }}">
                        @if ($parentTrade)
                            <input type="hidden" name="parent_trade_id" value="{{ $parentTrade->id }}">
                        @endif

                        @php
                            $lockSeason = now()->year;
                            $lockWeek = \App\Support\Season::currentWeek($lockSeason);
                        @endphp

                        <div class="grid gap-8 sm:grid-cols-2">
                            <div>
                                <h3 class="eyebrow mb-3">{{ $myTeam->name }} sends</h3>
                                <div class="space-y-1">
                                    @foreach ($myPlayers as $player)
                                        @php $locked = $player->isLockedForWeek($lockSeason, $lockWeek); @endphp
                                        <label class="flex items-center gap-2 text-sm py-1.5 px-2 rounded {{ $locked ? 'opacity-40' : 'hover:bg-chalk/60 cursor-pointer' }}">
                                            <input type="checkbox" name="my_players[]" value="{{ $player->id }}" @checked(in_array($player->id, $preselectedMy)) @disabled($locked) class="rounded border-ink/20 text-turf focus:ring-turf">
                                            <x-player-link :player="$player" class="font-bold text-ink" />
                                            <span class="text-ink/40 font-mono text-xs">{{ $player->position }}</span>
                                            @if ($locked)
                                                <span class="text-ink/30 text-[0.6rem] font-bold uppercase tracking-wide">Locked</span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <h3 class="eyebrow mb-3">{{ $theirTeam->name }} sends</h3>
                                <div class="space-y-1">
                                    @foreach ($theirPlayers as $player)
                                        @php $locked = $player->isLockedForWeek($lockSeason, $lockWeek); @endphp
                                        <label class="flex items-center gap-2 text-sm py-1.5 px-2 rounded {{ $locked ? 'opacity-40' : 'hover:bg-chalk/60 cursor-pointer' }}">
                                            <input type="checkbox" name="their_players[]" value="{{ $player->id }}" @checked(in_array($player->id, $preselectedTheir)) @disabled($locked) class="rounded border-ink/20 text-turf focus:ring-turf">
                                            <x-player-link :player="$player" class="font-bold text-ink" />
                                            <span class="text-ink/40 font-mono text-xs">{{ $player->position }}</span>
                                            @if ($locked)
                                                <span class="text-ink/30 text-[0.6rem] font-bold uppercase tracking-wide">Locked</span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <x-primary-button type="submit">{{ $parentTrade ? 'Send Counter Offer' : 'Propose Trade' }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
