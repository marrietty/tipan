<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-medium tracking-wide text-gray-500">Your schedule</p>
        <h1 class="mt-1 text-3xl font-semibold text-gray-900">Appointments</h1>
        <p class="mt-2 text-gray-600 max-w-xl">
            The patients you are seeing. Open any visit to record a diagnosis and prescriptions.
        </p>
    </x-slot>

    @php
        $statusStyles = [
            'scheduled' => 'bg-primary-indigo/30 text-primary',
            'completed' => 'bg-gray-100 text-gray-600',
            'missed' => 'bg-amber-50 text-amber-700',
        ];
    @endphp

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('status'))
            <div class="mb-6 rounded-xl bg-primary-indigo/30 border border-primary-indigo px-4 py-3 text-sm text-primary-dark">
                {{ session('status') }}
            </div>
        @endif

        @forelse ($appointments as $appointment)
            @php
                $start = \Illuminate\Support\Carbon::parse($appointment->schedule->slot_start)->format('g:i A');
                $date = $appointment->schedule->available_date->format('D, M j, Y');
                $hasRecord = $appointment->medicalRecord !== null;
                $isScheduled = $appointment->status->name === 'scheduled';
            @endphp
            <div class="rounded-2xl bg-white border border-gray-200/80 px-6 py-5 mb-3">
                <div class="flex items-center justify-between gap-4">
                    <a href="{{ route('doctor.appointments.record.edit', $appointment) }}"
                       class="group min-w-0 flex-1 focus:outline-none">
                        <div class="text-lg font-medium text-gray-900 group-hover:text-primary-dark">
                            {{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}
                        </div>
                        <div class="mt-1 text-sm text-gray-500 tabular-nums">
                            {{ $date }} &middot; {{ $start }}
                        </div>
                    </a>

                    <div class="flex items-center gap-3 shrink-0">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $statusStyles[$appointment->status->name] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $appointment->status->display_name }}
                        </span>
                        <a href="{{ route('doctor.appointments.record.edit', $appointment) }}"
                           class="text-sm {{ $hasRecord ? 'text-gray-500' : 'text-primary font-medium' }} hover:text-gray-900">
                            {{ $hasRecord ? 'View record' : 'Add record' }}
                        </a>
                    </div>
                </div>

                {{-- Quick, low-friction outcome actions on scheduled visits only.
                     Completed/missed are terminal and show no controls. --}}
                @if ($isScheduled)
                    <div class="mt-4 flex items-center justify-end gap-2 border-t border-gray-100 pt-4">
                        <form method="POST" action="{{ route('doctor.appointments.transition', $appointment) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="missed">
                            <button type="submit"
                                    class="rounded-lg px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:bg-amber-50 hover:text-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                                Mark missed
                            </button>
                        </form>
                        <form method="POST" action="{{ route('doctor.appointments.transition', $appointment) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit"
                                    class="rounded-lg bg-primary px-4 py-1.5 text-sm font-medium text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                                Mark completed
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white/60 px-8 py-16 text-center">
                <h2 class="text-lg font-semibold text-gray-900">No appointments yet</h2>
                <p class="mt-2 text-gray-600">
                    When patients book with you, their visits appear here.
                </p>
            </div>
        @endforelse
    </div>
</x-app-layout>
