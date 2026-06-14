<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-medium tracking-wide text-gray-500">Patient</p>
        <h1 class="mt-1 text-3xl font-semibold text-gray-900">
            Good day, {{ Auth::user()->displayName() }}.
        </h1>
        <p class="mt-2 text-gray-600 max-w-xl">
            Find a doctor and book a time that works for you. Your appointments and
            records live here too.
        </p>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-2xl">
            <div class="rounded-2xl bg-white border border-gray-200/80 p-8">
                <h2 class="text-xl font-semibold text-gray-900">Book an appointment</h2>
                <p class="mt-2 text-gray-600">
                    Choose a doctor, see their open times, and confirm in a few taps.
                </p>

                <div class="mt-6">
                    {{-- Primary action. Points at booking once that screen lands. --}}
                    <a href="#"
                       class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-6 py-3 text-base font-medium text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2">
                        Find a doctor
                    </a>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <a href="#" class="block rounded-xl px-5 py-4 text-gray-700 transition hover:bg-white hover:border-gray-200/80 border border-transparent">
                    <div class="font-medium text-gray-900">My appointments</div>
                    <div class="mt-1 text-sm text-gray-500">Upcoming and past visits.</div>
                </a>
                <a href="#" class="block rounded-xl px-5 py-4 text-gray-700 transition hover:bg-white hover:border-gray-200/80 border border-transparent">
                    <div class="font-medium text-gray-900">My records</div>
                    <div class="mt-1 text-sm text-gray-500">Diagnoses and prescriptions.</div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
