<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-medium tracking-wide text-primary">Booked</p>
        <h1 class="mt-1 text-3xl font-semibold text-gray-900">You&rsquo;re all set</h1>
        <p class="mt-2 text-gray-600 max-w-xl">
            Your appointment is confirmed. Here are the details. We&rsquo;ll see you then.
        </p>
    </x-slot>

    @php
        $start = \Illuminate\Support\Carbon::parse($appointment->schedule->slot_start)->format('g:i A');
        $end = \Illuminate\Support\Carbon::parse($appointment->schedule->slot_end)->format('g:i A');
        $date = $appointment->schedule->available_date->format('l, F j, Y');
    @endphp

    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="rounded-2xl bg-white border border-gray-200/80 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Status</span>
                    <span class="inline-flex items-center rounded-full bg-primary-indigo/30 px-3 py-1 text-sm font-medium text-primary">
                        {{ $appointment->status->display_name }}
                    </span>
                </div>
            </div>

            <dl class="divide-y divide-gray-100">
                <div class="flex items-start justify-between px-8 py-5">
                    <dt class="text-sm text-gray-500">Patient</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">
                        {{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}
                    </dd>
                </div>
                <div class="flex items-start justify-between px-8 py-5">
                    <dt class="text-sm text-gray-500">Doctor</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">
                        Dr. {{ $appointment->doctor->first_name }} {{ $appointment->doctor->last_name }}
                        <span class="block font-normal text-gray-500">{{ $appointment->doctor->specialization->display_name }}</span>
                    </dd>
                </div>
                <div class="flex items-start justify-between px-8 py-5">
                    <dt class="text-sm text-gray-500">When</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">
                        {{ $date }}
                        <span class="block font-normal text-gray-500 tabular-nums">{{ $start }} &ndash; {{ $end }}</span>
                    </dd>
                </div>
                @if ($appointment->reason)
                    <div class="flex items-start justify-between px-8 py-5">
                        <dt class="text-sm text-gray-500">Reason</dt>
                        <dd class="text-sm text-gray-900 text-right max-w-xs">{{ $appointment->reason }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="mt-6 flex items-center justify-between">
            <a href="{{ route('patient.booking.doctors') }}"
               class="text-sm font-medium text-gray-500 transition hover:text-gray-900">
                Book another
            </a>
            <a href="{{ route('patient.dashboard') }}"
               class="inline-flex items-center justify-center rounded-xl bg-primary px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                Done
            </a>
        </div>
    </div>
</x-app-layout>
