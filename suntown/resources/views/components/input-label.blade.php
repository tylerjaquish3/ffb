@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-bold text-xs uppercase tracking-wide text-ink/70']) }}>
    {{ $value ?? $slot }}
</label>
