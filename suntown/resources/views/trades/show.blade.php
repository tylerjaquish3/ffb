<x-app-layout>
    <x-slot name="header">
        <div class="eyebrow mb-1">Trade Proposal</div>
        <h2 class="font-display text-4xl text-white tracking-wide">
            {{ $trade->proposerTeam->name }} &amp; {{ $trade->recipientTeam->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-turf/10 border border-turf/30 text-turf px-4 py-3 rounded-md text-sm font-medium">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="bg-endzone/10 border border-endzone/30 text-endzone px-4 py-3 rounded-md text-sm font-medium">{{ $errors->first() }}</div>
            @endif

            <div class="card-panel overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <p class="text-sm text-ink/50">
                            Proposed by <span class="font-bold text-ink">{{ $trade->proposerTeam->name }}</span>
                            ({{ $trade->proposerTeam->user->name }}) &middot; {{ $trade->created_at->diffForHumans() }}
                        </p>
                        <span @class([
                            'inline-flex items-center px-2.5 py-1 rounded font-mono text-xs font-bold uppercase tracking-wide',
                            'bg-gold/20 text-ink' => in_array($trade->status, ['pending', 'under_review']),
                            'bg-turf/20 text-turf' => $trade->status === 'accepted',
                            'bg-endzone/10 text-endzone' => in_array($trade->status, ['declined', 'cancelled', 'vetoed']),
                            'bg-ink/10 text-ink/50' => $trade->status === 'countered',
                        ])>{{ str_replace('_', ' ', $trade->status) }}</span>
                    </div>

                    <div class="grid gap-8 sm:grid-cols-2">
                        <div>
                            <h3 class="eyebrow mb-3">{{ $trade->proposerTeam->name }} sends</h3>
                            <div class="space-y-1">
                                @foreach ($proposerItems as $item)
                                    <div class="flex items-center gap-2 text-sm py-1.5 px-2">
                                        <x-player-link :player="$item->player" class="font-bold text-ink" />
                                        <span class="text-ink/40 font-mono text-xs">{{ $item->player->position }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <h3 class="eyebrow mb-3">{{ $trade->recipientTeam->name }} sends</h3>
                            <div class="space-y-1">
                                @foreach ($recipientItems as $item)
                                    <div class="flex items-center gap-2 text-sm py-1.5 px-2">
                                        <x-player-link :player="$item->player" class="font-bold text-ink" />
                                        <span class="text-ink/40 font-mono text-xs">{{ $item->player->position }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @if ($forcedDrops->isNotEmpty())
                        <div class="mt-6 pt-4 border-t border-dashed border-ink/10">
                            <h4 class="text-[0.65rem] font-bold uppercase tracking-widest text-ink/40 mb-2">Also Dropped (Roster Limit)</h4>
                            <div class="space-y-1">
                                @foreach ($forcedDrops as $item)
                                    <div class="text-sm text-ink/60">
                                        <span class="font-bold">{{ $item->team->name }}</span> drops <x-player-link :player="$item->player" class="font-bold" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($trade->isUnderReview())
                        <div class="mt-6 pt-6 border-t border-ink/10">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-sm text-ink/60">
                                    Open to league veto until <span class="font-bold text-ink">{{ $trade->review_ends_at->format('D M j, g:ia') }}</span>
                                    ({{ $trade->review_ends_at->diffForHumans() }}).
                                </p>
                                <span class="record-chip">{{ $vetoCount }} / {{ \App\Models\Trade::VETO_THRESHOLD }} vetoes</span>
                            </div>

                            @if ($canVeto)
                                <form method="POST" action="{{ route('trades.veto', $trade) }}" onsubmit="return confirm('Cast a veto vote against this trade?')">
                                    @csrf
                                    <button type="submit" class="text-xs text-endzone hover:text-red-800 font-bold uppercase tracking-wide">Vote to Veto</button>
                                </form>
                            @endif
                        </div>
                    @endif

                    @if ($trade->status === 'countered' && $trade->counterTrade)
                        <p class="text-sm text-ink/50 mt-6">
                            Countered — <a href="{{ route('trades.show', $trade->counterTrade) }}" class="text-turf hover:text-turf-light font-bold">view the counter offer &rarr;</a>
                        </p>
                    @endif

                    @if ($trade->parentTrade)
                        <p class="text-sm text-ink/50 mt-2">
                            Countering <a href="{{ route('trades.show', $trade->parentTrade) }}" class="text-turf hover:text-turf-light font-bold">the original offer &rarr;</a>
                        </p>
                    @endif

                    @if ($canRespond || $canCancel)
                        <div class="mt-8 pt-6 border-t border-ink/10 flex items-center gap-3">
                            @if ($canRespond)
                                <form method="POST" action="{{ route('trades.accept', $trade) }}">
                                    @csrf
                                    <x-primary-button type="submit">Accept</x-primary-button>
                                </form>
                                @unless ($tradeDeadlinePassed)
                                    <a href="{{ route('trades.counter', $trade) }}">
                                        <x-secondary-button type="button">Counter</x-secondary-button>
                                    </a>
                                @endunless
                                <form method="POST" action="{{ route('trades.decline', $trade) }}" onsubmit="return confirm('Decline this trade?')">
                                    @csrf
                                    <button type="submit" class="text-xs text-endzone hover:text-red-800 font-bold uppercase tracking-wide">Decline</button>
                                </form>
                            @elseif ($canCancel)
                                <p class="text-sm text-ink/50">Waiting on {{ $trade->recipientTeam->name }} to respond.</p>
                                <form method="POST" action="{{ route('trades.cancel', $trade) }}" onsubmit="return confirm('Cancel this trade proposal?')">
                                    @csrf
                                    <button type="submit" class="text-xs text-endzone hover:text-red-800 font-bold uppercase tracking-wide">Cancel Proposal</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
