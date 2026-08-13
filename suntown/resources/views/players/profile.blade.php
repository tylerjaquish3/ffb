<div class="bg-chalk-white rounded-lg shadow-xl overflow-hidden max-h-[85vh] flex flex-col border-2 border-gold/80">
    {{-- Header band --}}
    <div class="relative bg-ink px-6 py-6 overflow-hidden shrink-0 border-b-2 border-gold/80">
        @if ($player->nflTeam)
            <img src="{{ $player->nflTeam->wordmarkImage() }}" alt="" aria-hidden="true"
                class="absolute -right-8 -bottom-10 w-72 opacity-[0.08] pointer-events-none select-none">
        @endif

        <button type="button" x-on:click="open = false" aria-label="Close"
            class="absolute top-3 right-3 z-10 text-white/40 hover:text-gold transition text-2xl leading-none">&times;</button>

        <div class="relative flex items-center gap-4">
            <div class="shrink-0 w-16 h-16 rounded-full bg-white/10 ring-2 ring-white/20 flex items-center justify-center overflow-hidden">
                @if ($player->nflTeam)
                    <img src="{{ $player->nflTeam->helmetImage() }}" alt="{{ $player->nflTeam->abbr }}" class="w-14 h-14 object-contain">
                @else
                    <span class="font-display text-white/30 text-xl tracking-wide">FA</span>
                @endif
            </div>
            <div class="min-w-0">
                <div class="font-display text-3xl sm:text-4xl text-white tracking-wide leading-none truncate pr-6">{{ $player->name }}</div>
                <div class="mt-2 flex items-center flex-wrap gap-x-2 gap-y-1 text-xs">
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-white/10 font-mono font-bold text-white tracking-wide">{{ $player->position }}</span>
                    @if ($player->nflTeam)
                        <span class="inline-flex items-center text-white/60 font-semibold">
                            <span class="team-dot mr-1.5" style="background-color: {{ $player->nflTeam->primary_color ?? '#999' }}"></span>{{ $player->nflTeam->abbr }}
                        </span>
                    @endif
                    <span class="text-white/25">&middot;</span>
                    @if ($ownerTeam)
                        <a href="{{ route('teams.show', $ownerTeam) }}" class="text-gold hover:text-white font-bold transition">{{ $ownerTeam->name }}</a>
                    @elseif ($waiverLockUntil)
                        <span class="text-white/40 font-bold uppercase tracking-wide">On Waivers &middot; clears {{ $waiverLockUntil->format('M j, g:ia') }}</span>
                    @else
                        <span class="text-white/40 font-bold uppercase tracking-wide">Free Agent</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Hero stat strip --}}
    <div class="bg-ink-2 px-6 py-3 flex items-center justify-between shrink-0 border-t border-white/5">
        <span class="eyebrow">{{ $season }} Season</span>
        <div class="flex items-baseline gap-1.5">
            <span class="led-digits text-2xl font-bold">{{ number_format($seasonPoints, 1) }}</span>
            <span class="text-white/30 text-[0.65rem] uppercase tracking-wide font-bold">pts</span>
        </div>
    </div>

    {{-- Game log --}}
    <div class="overflow-y-auto p-5 flex-1">
        <h3 class="eyebrow !text-ink/40 mb-3">Game Log</h3>

        @if ($weeks->isEmpty())
            <p class="text-ink/40 text-sm text-center py-8">No schedule available yet this season.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="text-[0.6rem] uppercase tracking-widest text-ink/30 font-bold">
                            <th class="py-1 pr-3"></th>
                            <th class="py-1 pr-3"></th>
                            <th class="py-1 pl-3"></th>
                            @foreach ($statGroups as $group)
                                <th class="py-1 px-2 text-center border-b border-ink/10" colspan="{{ $group['categories']->count() }}">{{ $group['label'] }}</th>
                            @endforeach
                        </tr>
                        <tr class="border-b-2 border-ink/10 text-ink/50 font-sans font-bold uppercase text-[0.65rem] tracking-wide">
                            <th class="py-1.5 pr-3 text-left">Wk</th>
                            <th class="py-1.5 pr-3 text-left">Opp</th>
                            <th class="py-1.5 pl-3 text-right">Pts</th>
                            @foreach ($statGroups as $group)
                                @foreach ($group['categories'] as $category)
                                    <th class="py-1.5 px-2 text-right whitespace-nowrap">{{ $category->label }}</th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="font-mono">
                        @foreach ($weeks as $row)
                            <tr class="border-b border-ink/5 hover:bg-chalk/60 {{ $row['isBye'] ?? false ? 'opacity-50' : '' }}">
                                <td class="py-1.5 pr-3 text-ink/70 font-bold tabular-nums">{{ $row['week'] }}</td>
                                <td class="py-1.5 pr-3 text-ink/50 whitespace-nowrap">
                                    @if ($row['isBye'] ?? false)
                                        <span class="text-ink/30 font-bold uppercase tracking-wide text-[0.65rem]">Bye</span>
                                    @elseif ($row['game'])
                                        {{ $row['game']['is_home'] ? 'vs' : '@' }} {{ $row['game']['opponent']->abbr }}
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td class="py-1.5 pl-3 text-right font-bold text-turf tabular-nums">
                                    {{ ($row['isBye'] ?? false) ? '—' : number_format($row['points'], 1) }}
                                </td>
                                @foreach ($statGroups as $group)
                                    @foreach ($group['categories'] as $category)
                                        <td class="py-1.5 px-2 text-right text-ink/70 tabular-nums">
                                            {{ ($row['isBye'] ?? false) ? '—' : number_format($row['valuesByCode']->get($category->code, 0), 0) }}
                                        </td>
                                    @endforeach
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Actions --}}
    @if (! $ownerTeam)
        <div class="border-t border-ink/10 bg-chalk px-5 py-3.5 flex justify-end shrink-0">
            @if ($waiverLockUntil)
                <a href="{{ route('waivers.create', $player) }}" class="inline-flex items-center px-4 py-2 bg-turf hover:bg-turf-light text-chalk-white font-bold text-sm rounded-md shadow-sm transition">Bid on Waivers</a>
            @else
                <a href="{{ route('players.add.create', $player) }}" class="inline-flex items-center px-4 py-2 bg-turf hover:bg-turf-light text-chalk-white font-bold text-sm rounded-md shadow-sm transition">+ Add to Roster</a>
            @endif
        </div>
    @elseif ($myTeam && $myTeam->id !== $ownerTeam->id)
        <div class="border-t border-ink/10 bg-chalk px-5 py-3.5 flex justify-end shrink-0">
            <a href="{{ route('trades.create', ['team' => $ownerTeam, 'player' => $player->id]) }}" class="inline-flex items-center px-4 py-2 border-2 border-gold text-gold-dim hover:bg-gold hover:text-ink font-bold text-sm rounded-md transition">Propose Trade</a>
        </div>
    @endif
</div>
