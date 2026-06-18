<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-medium tracking-wide text-primary">Doctor</p>
        <h1 class="mt-1 text-3xl font-semibold text-heading">
            Welcome, Dr. {{ Auth::user()->displayName() }}.
        </h1>
        <p class="mt-2 text-body max-w-xl">
            Open availability for patients to book, then see your schedule and
            record outcomes after each visit.
        </p>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-2xl">
            <div class="rounded-2xl bg-white border border-line p-8 shadow-sm">
                <h2 class="text-xl font-semibold text-heading">Open your schedule</h2>
                <p class="mt-2 text-body">
                    Add time slots so patients can book with you. Slots you have not
                    opened will not appear to anyone.
                </p>

                <div class="mt-6">
                    {{-- Primary action. --}}
                    <a href="{{ route('doctor.schedule.index') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-primary px-6 py-3 text-base font-medium text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                        Manage availability
                    </a>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <a href="{{ route('doctor.appointments.index') }}" class="block rounded-xl px-5 py-4 text-body transition hover:bg-white hover:border-line border border-transparent">
                    <div class="font-medium text-heading">Appointments</div>
                    <div class="mt-1 text-sm text-body">Who you are seeing, and record each visit.</div>
                </a>
                <a href="{{ route('doctor.schedule.index') }}" class="block rounded-xl px-5 py-4 text-body transition hover:bg-white hover:border-line border border-transparent">
                    <div class="font-medium text-heading">Your schedule</div>
                    <div class="mt-1 text-sm text-body">Open and manage your availability.</div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
