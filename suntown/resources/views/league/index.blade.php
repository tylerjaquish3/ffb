<x-app-layout>
    <x-slot name="header">
        <div class="eyebrow mb-1">{{ $season }} Season</div>
        <h2 class="font-display text-4xl text-white tracking-wide">
            League
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-turf/10 border border-turf/30 text-turf px-4 py-3 rounded-md text-sm font-medium">{{ session('status') }}</div>
            @endif

            @foreach ($underReviewTrades as $entry)
                @php $trade = $entry['trade']; @endphp
                <div class="bg-gold/10 border border-gold/40 text-ink px-4 py-3 rounded-md text-sm font-medium flex items-center justify-between gap-4">
                    <span>
                        Trade under review: <span class="font-bold">{{ $trade->proposerTeam->name }}</span> &amp; <span class="font-bold">{{ $trade->recipientTeam->name }}</span>
                        &middot; open to veto until {{ $trade->review_ends_at->format('M j, g:ia') }}
                        &middot; <span class="font-mono">{{ $trade->vetoes->count() }} / {{ \App\Models\Trade::VETO_THRESHOLD }}</span> votes
                    </span>
                    <div class="flex items-center gap-3 whitespace-nowrap">
                        @if ($entry['canVeto'])
                            <form method="POST" action="{{ route('trades.veto', $trade) }}" onsubmit="return confirm('Cast a veto vote against this trade?')">
                                @csrf
                                <button type="submit" class="text-xs text-endzone hover:text-red-800 font-bold uppercase tracking-wide">Vote to Veto</button>
                            </form>
                        @endif
                        <a href="{{ route('trades.show', $trade) }}" class="text-turf hover:text-turf-light font-bold uppercase text-xs tracking-wide">View &rarr;</a>
                    </div>
                </div>
            @endforeach

            <!-- Tabs -->
            <div class="border-b-2 border-ink/10 flex gap-6">
                @foreach (['standings' => 'Standings', 'playoffs' => 'Playoffs', 'transactions' => 'Transactions', 'settings' => 'Settings'] as $key => $label)
                    <a href="{{ route('dashboard', ['tab' => $key]) }}"
                        class="pb-3 -mb-0.5 text-sm font-bold uppercase tracking-wide border-b-2 transition {{ $tab === $key ? 'border-turf text-turf' : 'border-transparent text-ink/40 hover:text-ink/70' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            @if ($tab === 'standings')
                <div class="space-y-8">
                    <!-- Standings -->
                    <div class="card-panel overflow-hidden">
                        <div class="p-6">
                            <h3 class="eyebrow mb-4">Standings</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm text-left">
                                    <thead>
                                        <tr class="border-b-2 border-ink/10 text-ink/50 font-sans font-bold uppercase text-xs tracking-wide">
                                            <th class="py-2 pr-4">Rank</th>
                                            <th class="py-2 pr-4">Team</th>
                                            <th class="py-2 pr-4">Manager</th>
                                            <th class="py-2 pr-4">Record</th>
                                            <th class="py-2 pr-4 text-right">PF</th>
                                            <th class="py-2 pr-4 text-right">PA</th>
                                            <th class="py-2 pr-4 text-right">Streak</th>
                                            <th class="py-2 pr-4 text-right">FAB</th>
                                            <th class="py-2 pr-4 text-right">Moves</th>
                                            <th class="py-2 pr-4 text-right">Trades</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($standings as $i => $row)
                                            <tr class="border-b border-ink/5 hover:bg-chalk/60">
                                                <td class="py-2.5 pr-4"><span class="rank-badge">{{ $i + 1 }}</span></td>
                                                <td class="py-2.5 pr-4 font-bold">
                                                    <a href="{{ route('teams.show', $row['team']) }}" class="text-ink hover:text-turf transition">
                                                        {{ $row['team']->name }}
                                                    </a>
                                                </td>
                                                <td class="py-2.5 pr-4 text-ink/50 font-medium">{{ $row['team']->user->name }}</td>
                                                <td class="py-2.5 pr-4">
                                                    <span class="record-chip">{{ $row['wins'] }}-{{ $row['losses'] }}@if($row['ties'])-{{ $row['ties'] }}@endif</span>
                                                </td>
                                                <td class="py-2.5 pr-4 text-right font-mono tabular-nums">{{ number_format($row['points_for'], 1) }}</td>
                                                <td class="py-2.5 pr-4 text-right font-mono tabular-nums text-ink/40">{{ number_format($row['points_against'], 1) }}</td>
                                                <td class="py-2.5 pr-4 text-right font-mono tabular-nums">{{ $row['streak'] }}</td>
                                                <td class="py-2.5 pr-4 text-right font-mono tabular-nums">${{ $row['fab'] }}</td>
                                                <td class="py-2.5 pr-4 text-right font-mono tabular-nums text-ink/40">{{ $row['moves'] }}</td>
                                                <td class="py-2.5 pr-4 text-right font-mono tabular-nums text-ink/40">{{ $row['trades'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Matchups -->
                    <div class="card-panel overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-5">
                                <h3 class="eyebrow">Matchups</h3>
                                <x-week-nav :week="$week"
                                    :prev-url="route('dashboard', ['tab' => 'standings', 'season' => $season, 'week' => max(1, $week - 1)])"
                                    :next-url="route('dashboard', ['tab' => 'standings', 'season' => $season, 'week' => $week + 1])" />
                            </div>

                            @if ($matchups->isEmpty())
                                <p class="text-ink/50 text-sm">No matchups scheduled for this week yet.</p>
                            @else
                                <div class="grid gap-4 sm:grid-cols-2">
                                    @foreach ($matchups as $matchup)
                                        @php
                                            $homeScore = $matchup->homeScore();
                                            $awayScore = $matchup->awayScore();
                                        @endphp
                                        <a href="{{ route('matchups.show', $matchup) }}" class="block border-2 border-ink/10 rounded-lg p-4 hover:border-turf hover:shadow-md transition bg-chalk-white">
                                            <div class="flex justify-between items-center">
                                                <span class="font-bold {{ $homeScore >= $awayScore ? 'text-ink' : 'text-ink/40' }}">{{ $matchup->homeTeam->name }}</span>
                                                <span class="font-mono tabular-nums {{ $homeScore >= $awayScore ? 'text-turf font-bold' : 'text-ink/40' }}">{{ number_format($homeScore, 1) }}</span>
                                            </div>
                                            <div class="my-1.5 border-t border-dashed border-ink/10"></div>
                                            <div class="flex justify-between items-center">
                                                <span class="font-bold {{ $awayScore >= $homeScore ? 'text-ink' : 'text-ink/40' }}">{{ $matchup->awayTeam->name }}</span>
                                                <span class="font-mono tabular-nums {{ $awayScore >= $homeScore ? 'text-turf font-bold' : 'text-ink/40' }}">{{ number_format($awayScore, 1) }}</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif ($tab === 'playoffs')
                <div class="card-panel overflow-hidden">
                    <div class="p-6">
                        <h3 class="eyebrow mb-1">Projected Playoff Bracket</h3>
                        <p class="text-sm text-ink/50 mb-6">Top {{ count($playoffSeeds) }} teams by current record — if the season ended today. Seeds 1-2 get a first-round bye, then the bracket reseeds: the 1 seed always plays the lowest remaining seed.</p>

                        @if (! $playoffBracket)
                            <p class="text-ink/50 text-sm">Not enough teams with a scheduled season yet.</p>
                        @else
                            <div class="space-y-5">
                                {{-- Round 1: the 1 and 2 seed byes bracket the two quarterfinals, top and bottom --}}
                                <div>
                                    <div class="text-[0.65rem] font-bold uppercase tracking-widest text-ink/40 mb-3">Round 1</div>
                                    <div class="max-w-md mx-auto w-full space-y-3">
                                        @foreach ($playoffBracket['round1'] as $slot)
                                            @if ($slot['type'] === 'bye')
                                                <div class="border-2 border-gold/30 rounded-lg p-4 bg-chalk-white flex items-center justify-between gap-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="rank-badge">{{ $slot['seed'] }}</span>
                                                        <a href="{{ route('teams.show', $slot['row']['team']) }}" class="font-bold text-ink hover:text-turf transition">{{ $slot['row']['team']->name }}</a>
                                                    </div>
                                                    <span class="text-[0.65rem] font-bold uppercase tracking-wide text-gold-dim">Bye</span>
                                                </div>
                                            @else
                                                <div class="border-2 border-ink/10 rounded-lg p-4 bg-chalk-white space-y-2">
                                                    <div class="text-[0.65rem] font-bold uppercase tracking-widest text-ink/40 mb-1">Quarterfinal</div>
                                                    @foreach ($slot['pair'] as $entry)
                                                        <div class="flex items-center justify-between gap-2">
                                                            <div class="flex items-center gap-2">
                                                                <span class="rank-badge">{{ $entry['seed'] }}</span>
                                                                <a href="{{ route('teams.show', $entry['row']['team']) }}" class="font-bold text-ink hover:text-turf transition">{{ $entry['row']['team']->name }}</a>
                                                            </div>
                                                            <span class="record-chip">{{ $entry['row']['wins'] }}-{{ $entry['row']['losses'] }}@if($entry['row']['ties'])-{{ $entry['row']['ties'] }}@endif</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                                <div class="text-gold/40 font-display text-2xl text-center">&darr;</div>

                                {{-- Semifinals: reseeded once quarterfinal winners are known --}}
                                <div>
                                    <div class="text-[0.65rem] font-bold uppercase tracking-widest text-ink/40 mb-3">Semifinals &middot; Reseeded</div>
                                    <div class="grid sm:grid-cols-2 gap-4">
                                        <div class="border-2 border-ink/10 rounded-lg p-4 bg-chalk-white space-y-2">
                                            <div class="flex items-center gap-2">
                                                <span class="rank-badge">{{ $playoffBracket['byes'][0]['seed'] }}</span>
                                                <a href="{{ route('teams.show', $playoffBracket['byes'][0]['row']['team']) }}" class="font-bold text-ink hover:text-turf transition">{{ $playoffBracket['byes'][0]['row']['team']->name }}</a>
                                            </div>
                                            <div class="my-1.5 border-t border-dashed border-ink/10"></div>
                                            <div class="text-ink/40 text-sm font-bold py-1">Lowest Remaining Seed</div>
                                        </div>
                                        <div class="border-2 border-ink/10 rounded-lg p-4 bg-chalk-white space-y-2">
                                            <div class="flex items-center gap-2">
                                                <span class="rank-badge">{{ $playoffBracket['byes'][1]['seed'] }}</span>
                                                <a href="{{ route('teams.show', $playoffBracket['byes'][1]['row']['team']) }}" class="font-bold text-ink hover:text-turf transition">{{ $playoffBracket['byes'][1]['row']['team']->name }}</a>
                                            </div>
                                            <div class="my-1.5 border-t border-dashed border-ink/10"></div>
                                            <div class="text-ink/40 text-sm font-bold py-1">Highest Remaining Seed</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-gold/40 font-display text-2xl text-center">&darr;</div>

                                {{-- Championship --}}
                                <div class="max-w-sm mx-auto w-full border-2 border-gold/30 rounded-lg p-4 bg-chalk-white">
                                    <div class="text-[0.65rem] font-bold uppercase tracking-widest text-ink/40 mb-2">Championship</div>
                                    <div class="text-ink/40 text-sm font-bold py-1">Winner of Semifinal A</div>
                                    <div class="my-1.5 border-t border-dashed border-ink/10"></div>
                                    <div class="text-ink/40 text-sm font-bold py-1">Winner of Semifinal B</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                @if ($consolationBracket)
                    <div class="border-2 border-ink/10 rounded-lg overflow-hidden mt-6">
                        <div class="p-6">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-ink/40 mb-1">Consolation Bracket</h3>
                            <p class="text-sm text-ink/40 mb-6">Seeds 7-10 miss the playoffs. They sit out the quarterfinal round, then play their own semifinals and a 7th-place game over the same two weeks the championship bracket uses for its semifinals and championship.</p>

                            <div class="space-y-5">
                                <div>
                                    <div class="text-[0.65rem] font-bold uppercase tracking-widest text-ink/30 mb-3">Round 1 &middot; Week Off</div>
                                    <div class="max-w-md mx-auto w-full space-y-3">
                                        @foreach ($consolationSeeds as $index => $row)
                                            <div class="border-2 border-ink/10 rounded-lg p-4 bg-chalk-white flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="rank-badge">{{ 6 + $index + 1 }}</span>
                                                    <a href="{{ route('teams.show', $row['team']) }}" class="font-bold text-ink hover:text-turf transition">{{ $row['team']->name }}</a>
                                                </div>
                                                <span class="text-[0.65rem] font-bold uppercase tracking-wide text-ink/30">No Game</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="text-ink/20 font-display text-2xl text-center">&darr;</div>

                                <div>
                                    <div class="text-[0.65rem] font-bold uppercase tracking-widest text-ink/30 mb-3">Semifinals</div>
                                    <div class="grid sm:grid-cols-2 gap-4">
                                        @foreach ($consolationBracket['semifinals'] as $pair)
                                            <div class="border-2 border-ink/10 rounded-lg p-4 bg-chalk-white space-y-2">
                                                @foreach ($pair as $entry)
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-2">
                                                            <span class="rank-badge">{{ $entry['seed'] }}</span>
                                                            <a href="{{ route('teams.show', $entry['row']['team']) }}" class="font-bold text-ink hover:text-turf transition">{{ $entry['row']['team']->name }}</a>
                                                        </div>
                                                        <span class="record-chip">{{ $entry['row']['wins'] }}-{{ $entry['row']['losses'] }}@if($entry['row']['ties'])-{{ $entry['row']['ties'] }}@endif</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="text-ink/20 font-display text-2xl text-center">&darr;</div>

                                <div class="max-w-sm mx-auto w-full border-2 border-ink/10 rounded-lg p-4 bg-chalk-white">
                                    <div class="text-[0.65rem] font-bold uppercase tracking-widest text-ink/30 mb-2">7th Place Game</div>
                                    <div class="text-ink/40 text-sm font-bold py-1">Winner of Semifinal A</div>
                                    <div class="my-1.5 border-t border-dashed border-ink/10"></div>
                                    <div class="text-ink/40 text-sm font-bold py-1">Winner of Semifinal B</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @elseif ($tab === 'transactions')
                <div class="card-panel overflow-hidden">
                    <div class="p-6">
                        <h3 class="eyebrow mb-4">Transactions</h3>

                        <form method="GET" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                            <input type="hidden" name="tab" value="transactions">
                            <input type="hidden" name="season" value="{{ $season }}">

                            <select name="filter_team" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf">
                                <option value="">All Teams</option>
                                @foreach ($allTeams as $team)
                                    <option value="{{ $team->id }}" @selected($filterTeam === $team->id)>{{ $team->name }}</option>
                                @endforeach
                            </select>

                            <select name="filter_type" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf">
                                <option value="">All Types</option>
                                <option value="add" @selected($filterType === 'add')>Add</option>
                                <option value="drop" @selected($filterType === 'drop')>Drop</option>
                                <option value="trade" @selected($filterType === 'trade')>Trade</option>
                            </select>

                            <x-secondary-button type="submit">Filter</x-secondary-button>

                            @if ($filterTeam || $filterType)
                                <a href="{{ route('dashboard', ['tab' => 'transactions', 'season' => $season]) }}" class="text-xs text-ink/40 hover:text-ink/70 font-bold uppercase tracking-wide self-center">Clear</a>
                            @endif
                        </form>

                        @if ($transactions->isEmpty())
                            <p class="text-ink/50 text-sm">No adds, drops, or trades match these filters.</p>
                        @else
                            <div class="space-y-2">
                                @foreach ($transactions as $transaction)
                                    <div class="flex items-center justify-between gap-4 border-b border-ink/5 py-2 text-sm">
                                        <span>
                                            @if ($transaction->type === 'add')
                                                <span class="font-bold text-ink">{{ $transaction->team->name }}</span> added <x-player-link :player="$transaction->player" class="font-bold text-ink" />
                                            @elseif ($transaction->type === 'drop')
                                                <span class="font-bold text-ink">{{ $transaction->team->name }}</span> dropped <x-player-link :player="$transaction->player" class="font-bold text-ink" />
                                            @else
                                                <span class="font-bold text-ink">{{ $transaction->team->name }}</span> acquired <x-player-link :player="$transaction->player" class="font-bold text-ink" /> from <span class="font-bold text-ink">{{ $transaction->counterpartyTeam->name }}</span> (trade)
                                            @endif
                                        </span>
                                        <span class="text-ink/30 font-mono text-xs whitespace-nowrap">{{ $transaction->created_at->diffForHumans() }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @elseif ($tab === 'settings')
                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="card-panel overflow-hidden">
                        <div class="p-6">
                            <h3 class="eyebrow mb-4">Roster Positions</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm text-left">
                                    <thead>
                                        <tr class="border-b-2 border-ink/10 text-ink/50 font-sans font-bold uppercase text-xs tracking-wide">
                                            <th class="py-2 pr-4">Slot</th>
                                            <th class="py-2 pr-4">Eligible</th>
                                            <th class="py-2 pr-4 text-right">Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($rosterPositions as $rosterPosition)
                                            <tr class="border-b border-ink/5">
                                                <td class="py-2 pr-4 font-bold text-ink">{{ $rosterPosition->label }} <span class="text-ink/40 font-mono text-xs font-normal">{{ $rosterPosition->code }}</span></td>
                                                <td class="py-2 pr-4 text-ink/60 font-mono text-xs">{{ implode(', ', $rosterPosition->eligible_positions ?? []) }}</td>
                                                <td class="py-2 pr-4 text-right font-mono tabular-nums">{{ $rosterPosition->slot_count }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-xs text-ink/40 mt-4 font-mono">Roster limit: {{ \App\Models\RosterPosition::rosterLimit() }} players (IR doesn't count against the limit)</p>
                        </div>
                    </div>

                    <div class="card-panel overflow-hidden">
                        <div class="p-6">
                            <h3 class="eyebrow mb-4">Stat Categories</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm text-left">
                                    <thead>
                                        <tr class="border-b-2 border-ink/10 text-ink/50 font-sans font-bold uppercase text-xs tracking-wide">
                                            <th class="py-2 pr-4">Category</th>
                                            <th class="py-2 pr-4">Eligible</th>
                                            <th class="py-2 pr-4 text-right">Base</th>
                                            <th class="py-2 pr-4 text-right">Pts / Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($statCategories as $statCategory)
                                            <tr class="border-b border-ink/5">
                                                <td class="py-2 pr-4 font-bold text-ink">{{ $statCategory->label }}</td>
                                                <td class="py-2 pr-4 text-ink/60 font-mono text-xs">{{ implode(', ', $statCategory->eligible_positions ?? []) }}</td>
                                                <td class="py-2 pr-4 text-right font-mono tabular-nums text-ink/60">{{ $statCategory->base_points != 0 ? rtrim(rtrim(number_format($statCategory->base_points, 3), '0'), '.') : '—' }}</td>
                                                <td class="py-2 pr-4 text-right font-mono tabular-nums">{{ rtrim(rtrim(number_format($statCategory->points_per_unit, 3), '0'), '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if (Auth::user()->is_commissioner)
                        <p class="text-sm text-ink/50 lg:col-span-2">
                            Editable in <a href="{{ route('admin.roster-positions.index') }}" class="text-turf hover:text-turf-light font-bold">Commissioner Tools &rarr;</a>
                        </p>
                    @endif
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
