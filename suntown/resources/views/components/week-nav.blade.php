@props(['week', 'prevUrl' => null, 'nextUrl' => null])

<div class="inline-flex items-center gap-3 bg-ink rounded-lg px-3 py-1.5 shadow-panel">
    @if ($prevUrl)
        <a href="{{ $prevUrl }}" aria-label="Previous week" class="text-white/40 hover:text-gold transition px-1">&#9664;</a>
    @else
        <span aria-hidden="true" class="text-white/15 px-1">&#9664;</span>
    @endif
    <div class="text-center leading-none px-1">
        <div class="font-sans text-[0.6rem] font-bold uppercase tracking-widest text-white/40">Week</div>
        <div class="led-digits text-xl font-bold">{{ sprintf('%02d', $week) }}</div>
    </div>
    @if ($nextUrl)
        <a href="{{ $nextUrl }}" aria-label="Next week" class="text-white/40 hover:text-gold transition px-1">&#9654;</a>
    @else
        <span aria-hidden="true" class="text-white/15 px-1">&#9654;</span>
    @endif
</div>
