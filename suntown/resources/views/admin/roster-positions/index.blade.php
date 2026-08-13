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
                <h3 class="font-display text-2xl text-ink tracking-wide">Roster Positions</h3>
                <p class="text-sm text-ink/50">These define the slots each team's weekly lineup is built from (e.g. 1 QB, 2 RB, 1 FLEX, 6 BN). "BN" (bench) slots accept any position.</p>

                @foreach ($rosterPositions as $rosterPosition)
                    <div class="border rounded-lg p-4 flex flex-wrap items-end gap-3">
                        <form method="POST" action="{{ route('admin.roster-positions.update', $rosterPosition) }}" class="flex flex-wrap items-end gap-3">
                            @csrf
                            @method('PUT')

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Code</label>
                                <input type="text" name="code" value="{{ $rosterPosition->code }}" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-24">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Label</label>
                                <input type="text" name="label" value="{{ $rosterPosition->label }}" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-40">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Eligible</label>
                                <div class="flex gap-2 pt-1">
                                    @foreach ($availablePositions as $position)
                                        <label class="text-xs flex items-center gap-1">
                                            <input type="checkbox" name="eligible_positions[]" value="{{ $position }}" @checked(in_array($position, $rosterPosition->eligible_positions))>
                                            {{ $position }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Count</label>
                                <input type="number" name="slot_count" value="{{ $rosterPosition->slot_count }}" min="1" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-16">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Sort</label>
                                <input type="number" name="sort_order" value="{{ $rosterPosition->sort_order }}" min="0" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-16">
                            </div>
                            <x-secondary-button type="submit">Save</x-secondary-button>
                        </form>
                        <form method="POST" action="{{ route('admin.roster-positions.destroy', $rosterPosition) }}" onsubmit="return confirm('Delete this roster slot? This also removes any lineup entries using it.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Delete {{ $rosterPosition->code }}" class="p-2 rounded-md text-ink/40 hover:text-endzone hover:bg-endzone/10 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                    <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </form>
                    </div>
                @endforeach

                <form method="POST" action="{{ route('admin.roster-positions.store') }}" class="border-2 border-dashed border-turf/40 rounded-lg p-4 flex flex-wrap items-end gap-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Code</label>
                        <input type="text" name="code" placeholder="e.g. FLEX" required class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-24">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Label</label>
                        <input type="text" name="label" placeholder="e.g. Flex" required class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-40">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Eligible</label>
                        <div class="flex gap-2 pt-1">
                            @foreach ($availablePositions as $position)
                                <label class="text-xs flex items-center gap-1">
                                    <input type="checkbox" name="eligible_positions[]" value="{{ $position }}">
                                    {{ $position }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Count</label>
                        <input type="number" name="slot_count" value="1" min="1" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-16">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-ink/50">Sort</label>
                        <input type="number" name="sort_order" value="0" min="0" class="border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf w-16">
                    </div>
                    <x-primary-button type="submit">Add Slot</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
