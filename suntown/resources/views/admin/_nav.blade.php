<div class="mb-6 border-b-2 border-ink/10">
    <nav class="-mb-px flex space-x-6 overflow-x-auto">
        @foreach ([
            'admin.roster-positions.index' => 'Roster Positions',
            'admin.stat-categories.index' => 'Stat Categories',
            'admin.draft-import.index' => 'Import Jobs',
            'admin.schedule.index' => 'Schedule',
            'admin.stats.index' => 'Enter Stats',
            'admin.league-settings.edit' => 'League Settings',
        ] as $route => $label)
            <a href="{{ route($route) }}"
               class="whitespace-nowrap py-3 px-1 border-b-2 text-xs font-bold uppercase tracking-wide {{ request()->routeIs($route) ? 'border-gold text-turf' : 'border-transparent text-ink/40 hover:text-ink hover:border-ink/20' }}">
                {{ $label }}
            </a>
        @endforeach
    </nav>
</div>
