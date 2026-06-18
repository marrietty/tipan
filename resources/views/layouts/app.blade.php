<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <style>[x-cloak]{display:none !important;}</style>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:300,400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-heading">
        <div class="min-h-screen flex flex-col bg-canvas">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header>
                    <div class="max-w-7xl mx-auto pt-10 pb-2 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="border-t border-line bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <x-application-logo class="h-8 w-auto" />
                        <span class="font-bold tracking-wide text-heading">TIPAN</span>
                    </div>
                    <p class="text-sm text-body">&copy; {{ date('Y') }} {{ config('app.name', 'TIPAN') }} Medical Management System. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </body>
</html>
