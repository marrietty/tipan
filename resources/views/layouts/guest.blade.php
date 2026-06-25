<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Tipan') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:300,400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-heading antialiased">
        <div class="min-h-screen flex items-center justify-center bg-canvas p-4 sm:p-6 lg:p-8">
            <div class="w-full max-w-5xl grid grid-cols-1 lg:grid-cols-2 bg-surface rounded-2xl shadow-xl overflow-hidden">

                <!-- Brand panel -->
                <div class="relative hidden lg:flex flex-col justify-between p-10 text-white bg-brand-gradient overflow-hidden">
                    <div class="relative z-10">
                        <a href="/" class="flex items-center gap-3">
                            <x-application-logo class="h-10 w-auto" />
                            <span class="text-2xl font-bold tracking-wide">TIPAN</span>
                        </a>

                        <h2 class="mt-12 text-3xl font-bold leading-snug">
                            Secure Healthcare<br>Anytime, Anywhere
                        </h2>
                        <p class="mt-4 max-w-sm text-white/80 leading-relaxed">
                            Sign in to your account and continue your healthcare journey with us.
                        </p>
                    </div>

                    <!-- Decorative accents -->
                    <div class="pointer-events-none absolute -bottom-16 -right-16 h-64 w-64 rounded-full bg-white/10"></div>
                    <div class="pointer-events-none absolute top-24 -right-8 h-32 w-32 rounded-full bg-white/10"></div>
                </div>

                <!-- Form panel -->
                <div class="p-8 sm:p-10 lg:p-12">
                    <!-- Mobile logo (brand panel is hidden on small screens) -->
                    <a href="/" class="lg:hidden flex items-center justify-center gap-2 mb-8">
                        <x-application-logo class="h-9 w-auto" />
                        <span class="text-xl font-bold tracking-wide text-heading">TIPAN</span>
                    </a>

                    @isset($heading)
                        <div class="text-center mb-8">
                            <h1 class="text-2xl font-bold text-heading">{{ $heading }}</h1>
                            @isset($subheading)
                                <p class="mt-1 text-sm text-body">{{ $subheading }}</p>
                            @endisset
                        </div>
                    @endisset

                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
