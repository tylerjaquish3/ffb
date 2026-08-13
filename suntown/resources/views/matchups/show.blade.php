<x-app-layout>
    <x-slot name="header">
        <div class="eyebrow text-center mb-2">Week {{ $matchup->week }} &middot; {{ $matchup->season }} Season</div>
        <div class="flex items-center justify-center mb-3">
            <x-week-nav :week="$week"
                :prev-url="$prevMatchup ? route('matchups.show', $prevMatchup) : null"
                :next-url="$nextMatchup ? route('matchups.show', $nextMatchup) : null" />
        </div>
        <div class="flex items-center justify-center gap-6 sm:gap-12">
            <a href="{{ route('teams.show', $matchup->homeTeam) }}" class="text-center group">
                <div class="font-display text-2xl sm:text-4xl text-white tracking-wide group-hover:text-gold transition">{{ $matchup->homeTeam->name }}</div>
                <div class="text-xs text-white/40 font-medium mb-1">{{ $matchup->homeTeam->user->name }}</div>
                <div class="led-digits text-3xl sm:text-5xl font-bold {{ $homeScore < $awayScore ? 'opacity-40' : '' }}">{{ number_format($homeScore, 1) }}</div>
            </a>
            <div class="font-display text-xl sm:text-2xl text-gold/60">VS</div>
            <a href="{{ route('teams.show', $matchup->awayTeam) }}" class="text-center group">
                <div class="font-display text-2xl sm:text-4xl text-white tracking-wide group-hover:text-gold transition">{{ $matchup->awayTeam->name }}</div>
                <div class="text-xs text-white/40 font-medium mb-1">{{ $matchup->awayTeam->user->name }}</div>
                <div class="led-digits text-3xl sm:text-5xl font-bold {{ $awayScore < $homeScore ? 'opacity-40' : '' }}">{{ number_format($awayScore, 1) }}</div>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-turf/10 border border-turf/30 text-turf px-4 py-3 rounded-md text-sm font-medium">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="bg-endzone/10 border border-endzone/30 text-endzone px-4 py-3 rounded-md text-sm font-medium">{{ $errors->first() }}</div>
            @endif

            <div class="card-panel overflow-hidden">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b-2 border-ink/10 text-ink/50 font-sans font-bold uppercase text-xs tracking-wide">
                                    <th class="py-2 pr-4 text-left">{{ $matchup->homeTeam->name }}</th>
                                    <th class="py-2 pr-4 text-right">Pts</th>
                                    <th class="py-2 px-4 text-center">Slot</th>
                                    <th class="py-2 pl-4 text-right">Pts</th>
                                    <th class="py-2 pl-4 text-left">{{ $matchup->awayTeam->name }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $row)
                                    @php
                                        $homePlayer = $row['home']['player'] ?? null;
                                        $awayPlayer = $row['away']['player'] ?? null;
                                    @endphp
                                    <tr class="border-b border-ink/5 {{ $row['bench'] ? 'bg-chalk/50' : '' }}">
                                        <td class="py-2 pr-4 text-left">
                                            <div class="font-bold {{ $row['bench'] ? 'text-ink/50' : 'text-ink' }}">
                                                @if ($homePlayer)
                                                    <x-player-link :player="$homePlayer" />
                                                @else
                                                    &mdash;
                                                @endif
                                            </div>
                                            @if ($homePlayer)
                                                <div class="text-ink/40 text-[0.65rem] font-medium normal-case">
                                                    @if (! $row['home']['game'])
                                                        <span class="text-ink/30 font-bold uppercase tracking-wide">Bye</span>
                                                    @elseif ($row['home']['game']['opponent'])
                                                        {{ $row['home']['game']['is_home'] ? 'vs' : '@' }} {{ $row['home']['game']['opponent']->abbr }}
                                                        &middot; {{ $row['home']['game']['kickoff_at']->format('D g:iA') }}
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-2 pr-4 text-right font-mono tabular-nums font-bold {{ $row['bench'] ? 'text-ink/30' : '' }}">{{ $row['home'] ? number_format($row['home']['points'], 1) : '-' }}</td>
                                        <td class="py-2 px-4 text-center">
                                            <span class="inline-flex items-center justify-center min-w-[3.25rem] px-1.5 py-0.5 rounded font-mono text-[0.65rem] font-bold tracking-wide {{ $row['bench'] ? 'bg-ink/10 text-ink/40' : 'bg-turf text-chalk-white' }}">
                                                {{ $row['label'] }}
                                            </span>
                                        </td>
                                        <td class="py-2 pl-4 text-right font-mono tabular-nums font-bold {{ $row['bench'] ? 'text-ink/30' : '' }}">{{ $row['away'] ? number_format($row['away']['points'], 1) : '-' }}</td>
                                        <td class="py-2 pl-4 text-left">
                                            <div class="font-bold {{ $row['bench'] ? 'text-ink/50' : 'text-ink' }}">
                                                @if ($awayPlayer)
                                                    <x-player-link :player="$awayPlayer" />
                                                @else
                                                    &mdash;
                                                @endif
                                            </div>
                                            @if ($awayPlayer)
                                                <div class="text-ink/40 text-[0.65rem] font-medium normal-case">
                                                    @if (! $row['away']['game'])
                                                        <span class="text-ink/30 font-bold uppercase tracking-wide">Bye</span>
                                                    @elseif ($row['away']['game']['opponent'])
                                                        {{ $row['away']['game']['is_home'] ? 'vs' : '@' }} {{ $row['away']['game']['opponent']->abbr }}
                                                        &middot; {{ $row['away']['game']['kickoff_at']->format('D g:iA') }}
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Smack Talk -->
            <div class="card-panel overflow-hidden">
                <div class="p-6">
                    <h3 class="eyebrow mb-4">Smack Talk</h3>

                    @if ($matchup->comments->isEmpty())
                        <p class="text-ink/40 text-sm mb-4">No trash talk yet. Be the first.</p>
                    @else
                        <div class="space-y-3 mb-5">
                            @foreach ($matchup->comments as $comment)
                                <div class="flex items-baseline gap-2 text-sm">
                                    <span class="font-display text-turf tracking-wide shrink-0">{{ $comment->user->name }}</span>
                                    <span class="text-ink/80 flex-1">{{ $comment->body }}</span>
                                    <span class="text-[0.65rem] text-ink/30 font-mono shrink-0">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('matchups.comments.store', $matchup) }}" class="flex gap-2">
                        @csrf
                        <input type="text" name="body" maxlength="1000" required placeholder="Talk your talk..."
                            class="flex-1 border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf">
                        <x-primary-button>Post</x-primary-button>
                    </form>
                </div>
            </div>

            <!-- Other Matchups This Week -->
            @if ($otherMatchups->isNotEmpty())
                <div class="card-panel overflow-hidden">
                    <div class="p-6">
                        <h3 class="eyebrow mb-4">Other Matchups &middot; Week {{ $week }}</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach ($otherMatchups as $other)
                                @php
                                    $otherHomeScore = $other->homeScore();
                                    $otherAwayScore = $other->awayScore();
                                @endphp
                                <a href="{{ route('matchups.show', $other) }}" class="block border-2 border-ink/10 rounded-lg p-4 hover:border-turf hover:shadow-md transition bg-chalk-white">
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold {{ $otherHomeScore >= $otherAwayScore ? 'text-ink' : 'text-ink/40' }}">{{ $other->homeTeam->name }}</span>
                                        <span class="font-mono tabular-nums {{ $otherHomeScore >= $otherAwayScore ? 'text-turf font-bold' : 'text-ink/40' }}">{{ number_format($otherHomeScore, 1) }}</span>
                                    </div>
                                    <div class="my-1.5 border-t border-dashed border-ink/10"></div>
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold {{ $otherAwayScore >= $otherHomeScore ? 'text-ink' : 'text-ink/40' }}">{{ $other->awayTeam->name }}</span>
                                        <span class="font-mono tabular-nums {{ $otherAwayScore >= $otherHomeScore ? 'text-turf font-bold' : 'text-ink/40' }}">{{ number_format($otherAwayScore, 1) }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
