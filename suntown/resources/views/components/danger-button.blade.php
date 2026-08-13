<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-endzone border border-transparent rounded font-sans font-bold text-xs text-white uppercase tracking-widest hover:bg-red-800 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-endzone focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
