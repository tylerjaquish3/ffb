<nav x-data="{ open: false }" class="bg-ink border-b-4 border-gold">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo / Wordmark -->
                <div class="shrink-0 flex items-center gap-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <x-application-logo class="block h-6 w-auto text-gold" />
                        <span class="font-display text-2xl leading-none text-white tracking-wide">SUNTOWN</span>
                        <span class="font-sans text-[0.6rem] font-bold uppercase tracking-widest text-gold self-end mb-1">FFB</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('League') }}
                    </x-nav-link>
                    @if (Auth::user()->team)
                        <x-nav-link :href="route('teams.show', Auth::user()->team)" :active="request()->routeIs('teams.show') && request()->route('team')?->id === Auth::user()->team->id">
                            {{ __('My Team') }}
                        </x-nav-link>
                        <x-nav-link :href="route('matchups.mine')" :active="request()->routeIs('matchups.show')">
                            {{ __('Matchup') }}
                        </x-nav-link>
                    @endif
                    <x-nav-link :href="route('players.index')" :active="request()->routeIs('players.index')">
                        {{ __('Players') }}
                    </x-nav-link>
                    @if (Auth::user()->is_commissioner)
                        <x-nav-link :href="route('admin.roster-positions.index')" :active="request()->routeIs('admin.*')">
                            {{ __('Commissioner') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-bold uppercase tracking-wide rounded-md text-white/70 hover:text-white focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @if (Auth::user()->team)
                            <x-dropdown-link :href="route('teams.show', Auth::user()->team)">
                                {{ __('My Team') }}
                            </x-dropdown-link>
                        @endif

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white/60 hover:text-white hover:bg-white/10 focus:outline-none focus:bg-white/10 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-ink">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('League') }}
            </x-responsive-nav-link>
            @if (Auth::user()->team)
                <x-responsive-nav-link :href="route('teams.show', Auth::user()->team)">
                    {{ __('My Team') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('matchups.mine')">
                    {{ __('Matchup') }}
                </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('players.index')" :active="request()->routeIs('players.index')">
                {{ __('Players') }}
            </x-responsive-nav-link>
            @if (Auth::user()->is_commissioner)
                <x-responsive-nav-link :href="route('admin.roster-positions.index')" :active="request()->routeIs('admin.*')">
                    {{ __('Commissioner') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-white/10">
            <div class="px-4">
                <div class="font-bold text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-white/50">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
