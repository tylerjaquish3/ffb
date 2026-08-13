@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-2 border-ink/15 bg-chalk-white focus:border-turf focus:ring-turf rounded-md shadow-sm font-sans']) }}>
