<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-medium tracking-wide text-gray-500">Doctor</p>
        <h1 class="mt-1 text-3xl font-semibold text-gray-900">
            Welcome, Dr. {{ Auth::user()->displayName() }}.
        </h1>
        <p class="mt-2 text-gray-600 max-w-xl">
            Open availability for patients to book, then see your schedule and
            record outcomes after each visit.
        </p>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-2xl">
            <div class="rounded-2xl bg-white border border-gray-200/80 p-8">
                <h2 class="text-xl font-semibold text-gray-900">Open your schedule</h2>
                <p class="mt-2 text-gray-600">
                    Add time slots so patients can book with you. Slots you have not
                    opened will not appear to anyone.
                </p>

                <div class="mt-6">
                    {{-- Primary action. --}}
                    <a href="{{ route('doctor.schedule.index') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-6 py-3 text-base font-medium text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2">
                        Manage availability
                    </a>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <a href="{{ route('doctor.appointments.index') }}" class="block rounded-xl px-5 py-4 text-gray-700 transition hover:bg-white hover:border-gray-200/80 border border-transparent">
                    <div class="font-medium text-gray-900">Appointments</div>
                    <div class="mt-1 text-sm text-gray-500">Who you are seeing, and record each visit.</div>
                </a>
                <a href="{{ route('doctor.schedule.index') }}" class="block rounded-xl px-5 py-4 text-gray-700 transition hover:bg-white hover:border-gray-200/80 border border-transparent">
                    <div class="font-medium text-gray-900">Your schedule</div>
                    <div class="mt-1 text-sm text-gray-500">Open and manage your availability.</div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
