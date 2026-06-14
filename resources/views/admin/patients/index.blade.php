<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-medium tracking-wide text-gray-500">Accounts</p>
        <h1 class="mt-1 text-3xl font-semibold text-gray-900">Patients</h1>
        <p class="mt-2 text-gray-600 max-w-xl">
            Registered patient accounts and how many appointments each has booked.
        </p>
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @forelse ($patients as $patient)
            <div class="flex items-center justify-between gap-4 rounded-2xl bg-white border border-gray-200/80 px-6 py-5 mb-3">
                <div>
                    <div class="text-lg font-medium text-gray-900">
                        {{ $patient->first_name }} {{ $patient->last_name }}
                    </div>
                    <div class="mt-0.5 text-sm text-gray-500">
                        {{ $patient->phone ?: 'No phone on file' }}
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm font-medium text-gray-900 tabular-nums">{{ $patient->appointments_count }}</div>
                    <div class="text-xs text-gray-500">{{ Str::plural('appointment', $patient->appointments_count) }}</div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white/60 px-8 py-16 text-center">
                <h2 class="text-lg font-semibold text-gray-900">No patients yet</h2>
                <p class="mt-2 text-gray-600">Patient accounts appear here once people register.</p>
            </div>
        @endforelse
    </div>
</x-app-layout>
