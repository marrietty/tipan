<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-medium tracking-wide text-primary">Patient</p>
        <h1 class="mt-1 text-3xl font-semibold text-heading">
            Good day, {{ Auth::user()->displayName() }}.
        </h1>
        <p class="mt-2 text-body max-w-xl">
            Find a doctor and book a time that works for you. Your appointments and
            records live here too.
        </p>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-2xl">
            <div class="rounded-2xl bg-white border border-line p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-heading">Book an appointment</h2>
                <p class="mt-2 text-body">
                    Choose a doctor, see their open times, and confirm in a few taps.
                </p>

                <div class="mt-6">
                    {{-- Primary action. --}}
                    <a href="{{ route('patient.booking.doctors') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-primary px-6 py-3 text-base font-medium text-white shadow-sm transition-all duration-200 hover:bg-primary-dark active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                        Find a doctor
                    </a>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <a href="{{ route('patient.appointments.index') }}" class="group flex items-center justify-between rounded-xl px-5 py-4 text-body bg-white/60 border border-line/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:bg-white hover:border-line">
                    <div class="flex items-start gap-4">
                        <div class="mt-0.5 text-primary">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <div class="font-medium text-heading">My appointments</div>
                            <div class="mt-1 text-sm text-body">Upcoming and past visits.</div>
                        </div>
                    </div>
                    <svg class="h-5 w-5 text-gray-300 transition-colors group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('patient.records.index') }}" class="group flex items-center justify-between rounded-xl px-5 py-4 text-body bg-white/60 border border-line/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-md hover:bg-white hover:border-line">
                    <div class="flex items-start gap-4">
                        <div class="mt-0.5 text-primary">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <div class="font-medium text-heading">My records</div>
                            <div class="mt-1 text-sm text-body">Diagnoses and prescriptions.</div>
                        </div>
                    </div>
                    <svg class="h-5 w-5 text-gray-300 transition-colors group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
