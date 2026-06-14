<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-medium tracking-wide text-gray-500">Admin</p>
        <h1 class="mt-1 text-3xl font-semibold text-gray-900">
            Hello, {{ Auth::user()->displayName() }}.
        </h1>
        <p class="mt-2 text-gray-600 max-w-xl">
            Manage doctor accounts, keep an eye on appointments across the clinic,
            and review how things are running.
        </p>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-2xl">
            <div class="rounded-2xl bg-white border border-gray-200/80 p-8">
                <h2 class="text-xl font-semibold text-gray-900">Add a doctor</h2>
                <p class="mt-2 text-gray-600">
                    Create a doctor account with their specialization and license
                    number so they can open availability.
                </p>

                <div class="mt-6">
                    {{-- Primary action. Points at doctor management once it lands. --}}
                    <a href="#"
                       class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-6 py-3 text-base font-medium text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2">
                        Add a doctor
                    </a>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <a href="#" class="block rounded-xl px-5 py-4 text-gray-700 transition hover:bg-white hover:border-gray-200/80 border border-transparent">
                    <div class="font-medium text-gray-900">All appointments</div>
                    <div class="mt-1 text-sm text-gray-500">Everything booked across the clinic.</div>
                </a>
                <a href="#" class="block rounded-xl px-5 py-4 text-gray-700 transition hover:bg-white hover:border-gray-200/80 border border-transparent">
                    <div class="font-medium text-gray-900">Patients</div>
                    <div class="mt-1 text-sm text-gray-500">Registered patient accounts.</div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
