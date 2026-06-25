@php
    $user = Auth::user();
    $role = $user->roleName();

    // Navigation links per role. Each entry: [route name, label, active pattern?].
    // The active pattern (routeIs wildcard) defaults to the route name.
    $links = match ($role) {
        'patient' => [
            ['patient.dashboard', 'Home'],
            ['patient.booking.doctors', 'Find a doctor', 'patient.booking.*'],
            ['patient.appointments.index', 'My appointments', 'patient.appointments.*'],
            ['patient.records.index', 'My records', 'patient.records.*'],
        ],
        'doctor' => [
            ['doctor.dashboard', 'Home'],
            ['doctor.appointments.index', 'Appointments', 'doctor.appointments.*'],
            ['doctor.schedule.index', 'Schedule', 'doctor.schedule.*'],
        ],
        'admin' => [
            ['admin.dashboard', 'Overview'],
            ['admin.doctors.index', 'Doctors', 'admin.doctors.*'],
            ['admin.patients.index', 'Patients', 'admin.patients.*'],
            ['admin.appointments.index', 'Appointments', 'admin.appointments.*'],
        ],
        default => [],
    };

    $home = $user->homeRoute();
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-line">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route($home) }}" class="flex items-center gap-2">
                        <x-application-logo class="block h-9 w-auto" />
                        <span class="text-xl font-bold tracking-wide text-heading">TIPAN</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @foreach ($links as $link)
                        <x-nav-link :href="route($link[0])" :active="request()->routeIs($link[2] ?? $link[0])">
                            {{ $link[1] }}
                        </x-nav-link>
                    @endforeach
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <div class="mr-4">
                    <x-theme-toggle />
                </div>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-body bg-white hover:text-heading focus:outline-none transition ease-in-out duration-150">
                            <div>{{ $user->displayName() }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-line">
                            <div class="text-sm font-medium text-heading">{{ $user->displayName() }}</div>
                            <div class="text-xs text-body">{{ $user->role->display_name }}</div>
                        </div>

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
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-muted hover:text-body hover:bg-canvas focus:outline-none focus:bg-canvas focus:text-body transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @foreach ($links as $link)
                <x-responsive-nav-link :href="route($link[0])" :active="request()->routeIs($link[2] ?? $link[0])">
                    {{ $link[1] }}
                </x-responsive-nav-link>
            @endforeach
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-line">
            <div class="px-4">
                <div class="font-medium text-base text-heading">{{ $user->displayName() }}</div>
                <div class="font-medium text-sm text-body">{{ $user->role->display_name }}</div>
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
