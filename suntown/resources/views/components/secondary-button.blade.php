<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-chalk-white border-2 border-ink rounded font-sans font-bold text-xs text-ink uppercase tracking-widest hover:bg-ink hover:text-chalk-white focus:outline-none focus:ring-2 focus:ring-gold focus:ring-offset-2 focus:ring-offset-chalk disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
