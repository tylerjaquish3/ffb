<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=bebas-neue|libre-franklin:400,500,600,700,800|jetbrains-mono:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-chalk text-ink">
        <div class="min-h-screen bg-chalk pb-12 sm:pb-0">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="stadium-header shadow-panel">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @include('layouts.mobile-nav')

        <!-- Player profile modal: mounted once, opened from anywhere via
             $dispatch('open-player-modal', { id }) -- see components/player-link.blade.php -->
        <div
            x-data="{
                open: false,
                loading: false,
                html: '',
                async load(id) {
                    this.loading = true
                    this.open = true
                    this.html = ''
                    const response = await fetch(`/players/${id}/profile`)
                    this.html = await response.text()
                    this.loading = false
                },
            }"
            x-on:open-player-modal.window="load($event.detail.id)"
            x-on:keydown.escape.window="open = false"
            x-init="$watch('open', value => document.body.classList.toggle('overflow-y-hidden', value))"
            x-show="open"
            class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
            style="display: none;"
        >
            <div
                x-show="open"
                x-on:click="open = false"
                class="fixed inset-0 bg-ink/70 transform transition-all"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            ></div>

            <div
                x-show="open"
                class="relative mb-6 transform transition-all sm:w-full sm:max-w-2xl sm:mx-auto"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
                <template x-if="loading">
                    <div class="bg-chalk-white rounded-lg shadow-xl overflow-hidden animate-pulse border-2 border-gold/80">
                        <div class="bg-ink h-28"></div>
                        <div class="bg-ink-2 h-10"></div>
                        <div class="p-5 space-y-2">
                            <div class="h-3 bg-ink/10 rounded w-full"></div>
                            <div class="h-3 bg-ink/10 rounded w-5/6"></div>
                            <div class="h-3 bg-ink/10 rounded w-4/6"></div>
                        </div>
                    </div>
                </template>
                <div x-show="!loading" x-html="html"></div>
            </div>
        </div>
    </body>
</html>
