@php
    $team = Auth::user()->team;
    $navItem = function (string $label, string $href, bool $active, string $iconPath) {
        $textClass = $active ? 'text-gold' : 'text-white/55';

        return '<a href="' . $href . '" class="flex items-center justify-center gap-1 ' . $textClass . '">'
            . '<svg class="h-4 w-4 shrink-0' . ($active ? ' drop-shadow-[0_0_4px_rgba(242,177,52,0.7)]' : '') . '" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="' . $iconPath . '" /></svg>'
            . '<span class="text-[0.65rem] font-bold uppercase tracking-wide">' . $label . '</span></a>';
    };
@endphp
<nav class="fixed inset-x-0 bottom-0 z-40 sm:hidden w-full h-12 bg-ink border-t-4 border-gold shadow-[0_-8px_20px_-12px_rgba(18,32,58,0.45)]">
    <div class="grid grid-cols-4 h-full">
        {!! $navItem('League', route('dashboard'), request()->routeIs('dashboard'), 'M2.25 12l8.954-8.955a1.125 1.125 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75') !!}

        {!! $navItem(
            'Team',
            route('teams.show', $team),
            request()->routeIs('teams.show') && request()->route('team')?->id === $team->id,
            'M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z'
        ) !!}

        {!! $navItem(
            'Matchup',
            route('matchups.mine'),
            request()->routeIs('matchups.show'),
            'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z'
        ) !!}

        {!! $navItem('Players', route('players.index'), request()->routeIs('players.index'), 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z') !!}
    </div>
</nav>
