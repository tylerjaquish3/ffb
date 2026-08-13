<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="eyebrow mb-1">{{ $team->user->name }}'s Roster</div>
                <div class="flex items-center gap-3">
                    <h2 class="font-display text-4xl text-white tracking-wide">
                        {{ $team->name }}
                    </h2>
                    @if ($canEdit)
                        <button type="button" x-data="" x-on:click="$dispatch('open-modal', 'edit-team-name')" class="text-white/40 hover:text-gold transition" aria-label="Edit team name">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793 3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
            @if ($canProposeTrade)
                <a href="{{ route('trades.create', $team) }}" class="shrink-0">
                    <x-secondary-button type="button">Propose Trade</x-secondary-button>
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-turf/10 border border-turf/30 text-turf px-4 py-3 rounded-md text-sm font-medium">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="bg-endzone/10 border border-endzone/30 text-endzone px-4 py-3 rounded-md text-sm font-medium">{{ $errors->first() }}</div>
            @endif

            @foreach ($pendingTrades as $trade)
                <div class="bg-gold/10 border border-gold/40 text-ink px-4 py-3 rounded-md text-sm font-medium flex items-center justify-between gap-4">
                    <span>
                        @if ($trade->recipient_team_id === $team->id)
                            <strong>{{ $trade->proposerTeam->name }}</strong> proposed a trade.
                        @else
                            Waiting on <strong>{{ $trade->recipientTeam->name }}</strong> to respond to your trade proposal.
                        @endif
                    </span>
                    <a href="{{ route('trades.show', $trade) }}" class="text-turf hover:text-turf-light font-bold uppercase text-xs tracking-wide whitespace-nowrap">Review &rarr;</a>
                </div>
            @endforeach

            @if ($canEdit)
                <x-modal name="edit-team-name" :show="$errors->has('name')" focusable>
                    <form method="POST" action="{{ route('teams.update', $team) }}" class="p-6">
                        @csrf
                        @method('PUT')

                        <h2 class="font-display text-2xl text-ink tracking-wide mb-1">Edit Team Name</h2>
                        <p class="text-sm text-ink/50 mb-4">This is how your team shows up across the league.</p>

                        <x-input-label for="name" value="Team Name" class="sr-only" />
                        <input type="text" id="name" name="name" value="{{ $team->name }}" maxlength="50" required autofocus
                            class="w-full border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf">
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />

                        <div class="mt-6 flex justify-end gap-3">
                            <x-secondary-button type="button" x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                            <x-primary-button type="submit">Save</x-primary-button>
                        </div>
                    </form>
                </x-modal>
            @endif

            <div class="card-panel overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="eyebrow">Week {{ $week }} Lineup</h3>
                        <div class="flex items-end gap-2">
                            @if ($matchup)
                                @php
                                    $matchupHomeScore = $matchup->homeScore();
                                    $matchupAwayScore = $matchup->awayScore();
                                @endphp
                                <div class="mr-1">
                                    <div class="text-[0.6rem] font-bold uppercase tracking-widest text-ink/40 mb-1 text-center">Week {{ $week }} Matchup</div>
                                    <a href="{{ route('matchups.show', $matchup) }}" class="flex items-center gap-2 bg-ink rounded-lg px-3 py-1.5 shadow-panel hover:ring-2 hover:ring-gold transition">
                                        <span class="font-mono text-sm font-bold whitespace-nowrap {{ $matchupHomeScore >= $matchupAwayScore ? 'text-white' : 'text-white/40' }}">{{ $matchup->homeTeam->name }} {{ number_format($matchupHomeScore, 1) }}</span>
                                        <span class="text-white/30 text-xs">&ndash;</span>
                                        <span class="font-mono text-sm font-bold whitespace-nowrap {{ $matchupAwayScore >= $matchupHomeScore ? 'text-white' : 'text-white/40' }}">{{ number_format($matchupAwayScore, 1) }} {{ $matchup->awayTeam->name }}</span>
                                    </a>
                                </div>
                            @endif
                            <x-week-nav :week="$week"
                                :prev-url="route('teams.show', ['team' => $team, 'season' => $season, 'week' => max(1, $week - 1)])"
                                :next-url="route('teams.show', ['team' => $team, 'season' => $season, 'week' => $week + 1])" />
                        </div>
                    </div>

                    <form method="POST" action="{{ route('teams.lineup.update', $team) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="season" value="{{ $season }}">
                        <input type="hidden" name="week" value="{{ $week }}">

                        <div class="space-y-8">
                            @foreach ($sections as $section)
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-widest text-ink/40 mb-2">{{ $section['label'] }}</h4>

                                    @if ($section['rows']->isEmpty())
                                        <p class="text-ink/40 text-sm">No {{ strtolower($section['label']) }} rostered.</p>
                                    @else
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full text-sm text-left">
                                                <thead>
                                                    <tr class="border-b-2 border-ink/10 text-ink/50 font-sans font-bold uppercase text-xs tracking-wide">
                                                        <th class="py-2 pr-4">Slot</th>
                                                        <th class="py-2 pr-4">Player</th>
                                                        <th class="py-2 pr-4 text-right">Points</th>
                                                        @foreach ($section['statCategories'] as $statCategory)
                                                            <th class="py-2 pr-4 text-right whitespace-nowrap">{{ $statCategory->label }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($section['rows'] as $row)
                                                        @php $isBench = $row['label'] === 'Bench'; @endphp
                                                        <tr class="border-b border-ink/5 {{ $isBench ? 'bg-chalk/50' : '' }}">
                                                            <td class="py-2 pr-4">
                                                                @if ($canEdit && ! $row['locked'])
                                                                    <select name="assignments[{{ $row['player']->id }}]" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-xs font-mono font-bold focus:border-turf focus:ring-turf">
                                                                        <option value="">BN</option>
                                                                        @foreach ($row['options'] as $option)
                                                                            <option value="{{ $option['value'] }}" @selected($row['value'] === $option['value'])>{{ $option['label'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                @else
                                                                    <span class="inline-flex items-center justify-center min-w-[3.25rem] px-1.5 py-0.5 rounded font-mono text-[0.65rem] font-bold tracking-wide {{ $isBench ? 'bg-ink/10 text-ink/40' : 'bg-turf text-chalk-white' }}">
                                                                        {{ $isBench ? 'BN' : $row['label'] }}
                                                                    </span>
                                                                    @if ($row['locked'])
                                                                        <span class="text-ink/30 text-[0.6rem] font-bold uppercase tracking-wide ml-1" title="Game started — locked until waivers clear Tuesday night">&#128274;</span>
                                                                    @endif
                                                                @endif
                                                            </td>
                                                            <td class="py-2 pr-4">
                                                                <div class="font-bold whitespace-nowrap {{ $isBench ? 'text-ink/50' : 'text-ink' }}">
                                                                    <x-player-link :player="$row['player']" />
                                                                    <span class="text-ink/40 font-mono text-xs font-normal">{{ $row['player']->position }}</span>
                                                                    @if ($canEdit && ! $row['locked'])
                                                                        <button type="button"
                                                                            onclick="if (confirm('Drop {{ $row['player']->name }}?')) { document.getElementById('drop-player-{{ $row['player']->id }}').submit(); }"
                                                                            class="text-ink/30 hover:text-endzone text-[0.65rem] font-bold uppercase tracking-wide font-sans normal-case ml-1">
                                                                            Drop
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                                <div class="text-ink/40 text-[0.65rem] font-medium whitespace-nowrap">
                                                                    @if (! $row['game'])
                                                                        <span class="text-ink/30 font-bold uppercase tracking-wide">Bye</span>
                                                                    @elseif ($row['game']['opponent'])
                                                                        {{ $row['game']['is_home'] ? 'vs' : '@' }} {{ $row['game']['opponent']->abbr }}
                                                                        &middot; {{ $row['game']['kickoff_at']->format('D g:i A') }}
                                                                    @endif
                                                                </div>
                                                            </td>
                                                            <td class="py-2 pr-4 text-right font-mono tabular-nums font-bold {{ $isBench ? 'text-ink/30' : 'text-ink' }}">
                                                                {{ number_format($row['points'], 1) }}
                                                            </td>
                                                            @foreach ($section['statCategories'] as $statCategory)
                                                                <td class="py-2 pr-4 text-right font-mono tabular-nums text-xs {{ $isBench ? 'text-ink/30' : 'text-ink/60' }}">
                                                                    @if ($row['stats']->has($statCategory->id))
                                                                        {{ rtrim(rtrim(number_format($row['stats'][$statCategory->id], 2), '0'), '.') }}
                                                                    @else
                                                                        &mdash;
                                                                    @endif
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if ($canEdit)
                            <div class="mt-5">
                                <x-primary-button>Save Lineup</x-primary-button>
                            </div>
                        @endif
                    </form>

                    @if ($canEdit)
                        @foreach ($sections as $section)
                            @foreach ($section['rows'] as $row)
                                <form id="drop-player-{{ $row['player']->id }}" method="POST" action="{{ route('teams.roster.drop', [$team, $row['player']]) }}" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endforeach
                        @endforeach
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
