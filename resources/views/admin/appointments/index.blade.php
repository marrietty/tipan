<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-medium tracking-wide text-gray-500">Activity</p>
        <h1 class="mt-1 text-3xl font-semibold text-gray-900">All appointments</h1>
        <p class="mt-2 text-gray-600 max-w-xl">
            Every appointment across the clinic, most recent first.
        </p>
    </x-slot>

    @php
        $statusStyles = [
            'scheduled' => 'bg-primary-indigo/30 text-primary',
            'completed' => 'bg-gray-100 text-gray-600',
            'missed' => 'bg-amber-50 text-amber-700',
        ];
    @endphp

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @forelse ($appointments as $appointment)
            @php
                $start = \Illuminate\Support\Carbon::parse($appointment->schedule->slot_start)->format('g:i A');
                $date = $appointment->schedule->available_date->format('M j, Y');
            @endphp
            <div class="flex items-center justify-between gap-4 rounded-2xl bg-white border border-gray-200/80 px-6 py-4 mb-2.5">
                <div class="min-w-0">
                    <div class="font-medium text-gray-900 truncate">
                        {{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}
                        <span class="text-gray-400 font-normal">with</span>
                        Dr. {{ $appointment->doctor->first_name }} {{ $appointment->doctor->last_name }}
                    </div>
                    <div class="mt-0.5 text-sm text-gray-500 tabular-nums">
                        {{ $date }} &middot; {{ $start }}
                        <span class="text-gray-300">&middot;</span>
                        {{ $appointment->doctor->specialization->display_name }}
                    </div>
                </div>
                <span class="shrink-0 inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $statusStyles[$appointment->status->name] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ $appointment->status->display_name }}
                </span>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white/60 px-8 py-16 text-center">
                <h2 class="text-lg font-semibold text-gray-900">No appointments yet</h2>
                <p class="mt-2 text-gray-600">Booked appointments across the clinic will appear here.</p>
            </div>
        @endforelse
    </div>
</x-app-layout>
