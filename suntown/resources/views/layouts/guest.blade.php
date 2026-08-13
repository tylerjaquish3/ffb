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
    <body class="font-sans text-ink antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-ink bg-[radial-gradient(circle_at_50%_-10%,rgba(242,177,52,0.12),transparent_55%)]">
            <div class="flex items-center gap-2">
                <x-application-logo class="w-10 h-10 text-gold" />
                <div>
                    <div class="font-display text-4xl leading-none text-white tracking-wide">SUNTOWN</div>
                    <div class="eyebrow text-center">Fantasy Football</div>
                </div>
            </div>

            <div class="w-full sm:max-w-md mt-8 px-6 py-6 card-panel overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
