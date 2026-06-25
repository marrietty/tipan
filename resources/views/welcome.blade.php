<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'TIPAN') }}: Secure Healthcare Anytime, Anywhere</title>

        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

        <style>[x-cloak]{display:none !important;}</style>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:300,400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Dark Mode Setup -->
        <script>
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>
    </head>
    <body class="font-sans antialiased text-heading bg-canvas dark:bg-gray-900 dark:text-gray-100 transition-colors duration-200">
        <div x-data="{ open: false }" class="min-h-screen">

            <!-- Header -->
            <header class="bg-canvas/80 backdrop-blur sticky top-0 z-30 border-b border-line">
                <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
                    <a href="/" class="flex items-center gap-2">
                        <x-application-logo class="h-9 w-auto" />
                        <span class="text-xl font-bold tracking-wide text-heading">TIPAN</span>
                    </a>

                    <div class="hidden md:flex items-center gap-8 text-sm font-medium text-body">
                        <a href="#home" class="text-primary">Home</a>
                        <a href="#services" class="hover:text-heading transition">Services</a>
                        <a href="#about" class="hover:text-heading transition">About</a>
                        <a href="#faq" class="hover:text-heading transition">FAQ</a>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="mr-2 hidden md:block">
                            <x-theme-toggle />
                        </div>
                        @auth
                            <a href="{{ url('/') }}" class="inline-flex items-center px-5 py-2 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-lg transition">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center px-5 py-2 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-lg transition">
                                Login
                            </a>
                        @endauth
                    </div>
                </nav>
            </header>

            <!-- Hero -->
            <section id="home" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-12">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <p class="text-sm font-semibold text-primary mb-3">Welcome to TIPAN</p>
                        <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight text-heading">
                            Secure Your<br>Healthcare<br>Anytime, Anywhere
                        </h1>
                        <p class="mt-6 max-w-md text-body leading-relaxed">
                            Book appointments, consult with trusted doctors, access your medical records and
                            manage your health, all in one secure platform.
                        </p>

                        <div class="mt-8 flex flex-wrap gap-4">
                            @auth
                                <a href="{{ url('/') }}" class="inline-flex items-center px-6 py-3 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition active:scale-95 duration-200">
                                    Go to Dashboard
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition active:scale-95 duration-200">
                                    Get Started
                                </a>
                            @endauth
                            <a href="#services" class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-line hover:bg-canvas text-primary font-semibold rounded-lg transition">
                                Learn More
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Hero visual -->
                    <div class="relative">
                        <div class="aspect-square max-w-md mx-auto rounded-full bg-brand-gradient opacity-90 flex items-center justify-center">
                            <x-application-logo class="h-40 w-auto drop-shadow-xl" />
                        </div>
                    </div>
                </div>
            </section>

            <!-- Services -->
            <section id="services" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-heading">Our Services</h2>
                    <p class="mt-2 text-body">Everything you need for a better healthcare experience.</p>
                </div>

                <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ([
                        ['Appointment Booking', 'Book appointments with doctors instantly and manage your schedule easily.', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['Medical Records', 'Access your medical history and records securely anytime, anywhere.', 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'],
                        ['Prescriptions', 'View the prescriptions and medications your doctor has issued.', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['Manage Appointments', 'Track your upcoming and past visits, and cancel when plans change.', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                    ] as [$title, $desc, $icon])
                        <div class="bg-white border border-line rounded-2xl p-6 shadow-sm hover:shadow-md transition text-center">
                            <div class="mx-auto h-12 w-12 rounded-xl bg-primary-indigo/40 flex items-center justify-center text-primary">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
                            </div>
                            <h3 class="mt-4 font-semibold text-heading">{{ $title }}</h3>
                            <p class="mt-2 text-sm text-body leading-relaxed">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- About -->
            <section id="about" class="bg-white border-y border-line">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                    <div class="text-center max-w-2xl mx-auto">
                        <h2 class="text-3xl font-bold text-heading">About TIPAN</h2>
                        <p class="mt-3 text-body leading-relaxed">
                            TIPAN is a medical management system that brings appointments, medical records,
                            and prescriptions together in one secure place, built to make healthcare easier
                            to access and manage for patients and providers alike.
                        </p>
                    </div>

                    <h3 class="mt-12 text-center text-sm font-semibold uppercase tracking-wide text-primary">Meet the Team</h3>
                    <div class="mt-6 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach ([
                            ['John Paul Curada', 'Lead Developer', 'JpCurada'],
                            ['Neo Ervine Geroda', 'Database Admin', 'neo-geroda'],
                            ['Marie Criz Zaragoza', 'Frontend Developer', 'marrietty'],
                            ['Eunice Lim', 'UI/UX Developer', 'yunitchi'],
                        ] as [$name, $role, $github])
                            <a href="https://github.com/{{ $github }}" target="_blank" rel="noopener noreferrer" class="group block bg-canvas border border-line rounded-2xl p-6 text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:border-primary-light">
                                <img src="https://github.com/{{ $github }}.png?size=128" alt="{{ $name }}" class="mx-auto h-16 w-16 rounded-full border-2 border-transparent group-hover:border-primary transition-colors">
                                <div class="mt-4 font-semibold text-heading group-hover:text-primary transition-colors">{{ $name }}</div>
                                <div class="mt-1 text-sm text-body">{{ $role }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>

            <!-- FAQ -->
            <section id="faq" class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-heading">Frequently Asked Questions</h2>
                    <p class="mt-2 text-body">Everything you need to know to get started.</p>
                </div>

                <div x-data="{ selected: null }" class="mt-10 space-y-3">
                    @foreach ([
                        ['How do I create an account?', 'Click Get Started (or Sign Up on the login screen) and register with your name, email, and a password. New accounts are set up as patient accounts so you can book appointments right away.'],
                        ['How do I book an appointment?', 'Once you are signed in, go to Find a doctor, pick a doctor, choose an open time slot from their availability, and confirm. You will get a confirmation page with the details of your booking.'],
                        ['Can I cancel an appointment?', 'Yes. Open My appointments from your dashboard, find the visit you want to change, and cancel it there. The time slot becomes available for others once cancelled.'],
                        ['Where can I see my medical records and prescriptions?', 'Your dashboard has My records, where you can view your medical history along with any prescriptions and medications your doctor has issued.'],
                        ['How do doctors and admins get accounts?', 'Doctor and admin accounts are created by an administrator rather than through public sign-up. If you are a provider, contact your clinic administrator to have an account set up for you.'],
                        ['Is my health information secure?', 'Access is protected by your account login, and you only see the records tied to your own account. Always keep your password private and sign out on shared devices.'],
                    ] as $i => [$question, $answer])
                        <div class="bg-white border border-line rounded-2xl overflow-hidden">
                            <button type="button"
                                    @click="selected = (selected === {{ $i }} ? null : {{ $i }})"
                                    class="w-full flex items-center justify-between gap-4 px-6 py-4 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                                    :aria-expanded="(selected === {{ $i }}).toString()">
                                <span class="font-medium text-heading">{{ $question }}</span>
                                <svg class="shrink-0 h-5 w-5 text-primary transition-transform duration-200"
                                     :class="selected === {{ $i }} ? 'rotate-180' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="selected === {{ $i }}" x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0">
                                <p class="px-6 pb-5 -mt-1 text-sm text-body leading-relaxed">{{ $answer }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- Footer -->
            <footer id="contact" class="border-t border-line bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <a href="/" class="flex items-center gap-2">
                        <x-application-logo class="h-8 w-auto" />
                        <span class="font-bold tracking-wide text-heading">TIPAN</span>
                    </a>
                    <p class="text-sm text-body">&copy; {{ date('Y') }} {{ config('app.name', 'TIPAN') }} Medical Management System. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </body>
</html>
