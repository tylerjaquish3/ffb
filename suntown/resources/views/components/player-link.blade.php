@props(['player'])

<button
    type="button"
    x-data
    x-on:click.stop="$dispatch('open-player-modal', { id: {{ $player->id }} })"
    {{ $attributes->merge(['class' => 'appearance-none bg-transparent border-0 p-0 m-0 text-left cursor-pointer hover:underline underline-offset-2 decoration-dotted transition']) }}
>{{ $player->name }}</button>
