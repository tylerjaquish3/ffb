<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2.5 bg-gold border border-transparent rounded font-sans font-extrabold text-xs text-ink uppercase tracking-widest hover:bg-gold-dim focus:bg-gold-dim active:bg-gold-dim focus:outline-none focus:ring-2 focus:ring-ink focus:ring-offset-2 focus:ring-offset-chalk transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
