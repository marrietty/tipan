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
                       class="inline-flex items-center justify-center rounded-xl bg-primary px-6 py-3 text-base font-medium text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                        Find a doctor
                    </a>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <a href="{{ route('patient.appointments.index') }}" class="block rounded-xl px-5 py-4 text-body transition hover:bg-white hover:border-line border border-transparent">
                    <div class="font-medium text-heading">My appointments</div>
                    <div class="mt-1 text-sm text-body">Upcoming and past visits.</div>
                </a>
                <a href="{{ route('patient.records.index') }}" class="block rounded-xl px-5 py-4 text-body transition hover:bg-white hover:border-line border border-transparent">
                    <div class="font-medium text-heading">My records</div>
                    <div class="mt-1 text-sm text-body">Diagnoses and prescriptions.</div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
