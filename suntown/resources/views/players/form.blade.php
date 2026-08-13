<x-app-layout>
    <x-slot name="header">
        <div class="eyebrow mb-1">Commissioner Tools</div>
        <h2 class="font-display text-4xl text-white tracking-wide">
            {{ $player->exists ? 'Edit Player' : 'Add Player' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div class="bg-endzone/10 border border-endzone/30 text-endzone px-4 py-3 rounded-md text-sm font-medium">{{ $errors->first() }}</div>
            @endif

            <div class="card-panel overflow-hidden p-6">
                <form method="POST" action="{{ $player->exists ? route('players.update', $player) : route('players.store') }}" class="space-y-4">
                    @csrf
                    @if ($player->exists)
                        @method('PUT')
                    @endif

                    <div>
                        <x-input-label for="name" value="Name" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $player->name) }}" required autofocus />
                    </div>

                    <div>
                        <x-input-label for="position" value="Position" />
                        <select id="position" name="position" required class="mt-1 block w-full border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf">
                            @foreach (\App\Models\Player::POSITIONS as $position)
                                <option value="{{ $position }}" @selected(old('position', $player->position) === $position)>{{ $position }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="nfl_team_id" value="NFL Team" />
                        <select id="nfl_team_id" name="nfl_team_id" class="mt-1 block w-full border-2 border-ink/15 bg-chalk-white rounded-md shadow-sm text-sm focus:border-turf focus:ring-turf">
                            <option value="">No NFL Team</option>
                            @foreach ($nflTeams as $nflTeam)
                                <option value="{{ $nflTeam->id }}" @selected((int) old('nfl_team_id', $player->nfl_team_id) === $nflTeam->id)>{{ $nflTeam->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <x-primary-button type="submit">{{ $player->exists ? 'Save Changes' : 'Add Player' }}</x-primary-button>
                        <a href="{{ route('players.index') }}" class="text-xs font-bold uppercase tracking-wide text-ink/40 hover:text-ink">Cancel</a>
                    </div>
                </form>
            </div>

            @if ($player->exists && ! $player->rosterPlayer)
                <form method="POST" action="{{ route('players.destroy', $player) }}" onsubmit="return confirm('Remove {{ $player->name }} from the player pool? This can't be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-bold uppercase tracking-wide text-endzone hover:text-red-800">Remove from Player Pool</button>
                </form>
            @endif

        </div>
    </div>
</x-app-layout>
